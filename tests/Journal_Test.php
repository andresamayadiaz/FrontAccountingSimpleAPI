<?php

use GuzzleHttp\Client;

require_once(__DIR__ . '/TestConfig.php');

require_once(TEST_PATH . '/TestEnvironment.php');
require_once(TEST_PATH . '/Crud_Base.php');

const JOURNAL_POST_DATA = array(
    'trans_date' => '2013-01-10',
    'document_date' => '2013-01-11',
    'event_date' => '2013-01-12',
    'currency' => 'USD',
    'document_ref' => 'INV_123456',
    'reference' => '',
    'memo' => 'Test memo',
    'items' => array(
        array(
            'account_code' => '1060',
            'amount' => '-100',
            'memo' => 'Test memo line 1'
        ),
        array(
            'account_code' => '5610',
            'amount' => '100',
            'memo' => 'Test memo line 2'
        )
    )
);

class JournalTest extends Crud_Base
{
    private $postData = JOURNAL_POST_DATA;
    
    private $putData;
    
    private $url;

    private $keyProperty;

    protected $method;

    const JSON = 'json';
    const FORM_DATA = 'form_params';

    /**
     * Constructor. The $putData will be set to $postData if not set
     * @param string $url url of the endpoint
     * @param string $keyProperty the property containing the key id
     * @param array $postData example data to use in post (create)
     * @param array $putData example data to use in put (update)
     */
    public function __construct()
    {
        $this->putData = $this->postData;
        $this->putData['document_ref'] = 'INV_NEW_123456';
        $this->putData['document_date'] = '2013-02-13';
        $this->putData['trans_date'] = '2013-02-14';
        $this->putData['event_date'] = '2013-02-15';
        $this->putData['memo'] = 'Test memo edited';
        unset($this->putData['items']);
        unset($this->putData['currency']);
        unset($this->putData['reference']);

        $this->url = '/modules/api/journal/';
        $this->keyProperty = 'id';

        $this->method = self::FORM_DATA;
    }

    protected function checkCountInitial($count, $result)
    {
        // $this->assertGreaterThan(0, $count); // TODO Not sure about the one here.  zero should be ok
    }

    protected function fixExpectedType($expected, $result)
    {
        $expectedNew = null;
        if (is_a($result, 'stdClass')) {
            $expectedNew = new \stdClass();
            foreach ($expected as $key => $value) {
                $expectedNew->{$key} = $value;
            }
        } else {
            $expectedNew = $expected;
        }
        return $expectedNew;
    }

    protected function removeKeyProperty($result)
    {
        if (isset($result->{$this->keyProperty})) {
            unset($result->{$this->keyProperty});
        }
        return $result;
    }

    protected function checkGetAfterPost($result)
    {
        $expected = $this->fixExpectedType($this->postData, $result);
        $expected->reference = '1';
        foreach ($expected->items as $key => $value) {
            $expected->items[$key] = $this->fixExpectedType($value, $result->items[$key]);
        }
        $result = $this->removeKeyProperty($result);
        unset($result->{'type'});
        $this->assertEquals($expected, $result, 'Failed GET after POST');
    }

    protected function checkGetAfterPut($result)
    {
        $expected = $this->fixExpectedType($this->putData, $result);
        $expected->currency = 'USD';
        $expected->reference = '1';
        $expected->items = $this->postData['items'];
        foreach ($expected->items as $key => $value) {
            $expected->items[$key] = $this->fixExpectedType($value, $result->items[$key]);
        }
        $result = $this->removeKeyProperty($result);
        unset($result->{'type'});
        $this->assertEquals($expected, $result, 'Failed GET after PUT');
    }

    protected function getAll()
    {
        $client = TestEnvironment::client();
        $response = $client->get($this->url, array(
            'headers' => TestEnvironment::headers()
        ));
        $this->assertEquals('200', $response->getStatusCode());
        $result = $response->getBody();
        $result = json_decode($result);
        return $result;
    }

    public function testCRUD_Ok()
    {
        $client = TestEnvironment::client();

        // List
        $result = $this->getAll();
        $count0 = count($result);
        $this->checkCountInitial($count0, $result);

        // Add
        $response = $client->post($this->url, array(
            'headers' => TestEnvironment::headers(),
            $this->method => $this->postData
        ));
        $this->assertEquals('201', $response->getStatusCode());
        $result = $response->getBody();
        $result = json_decode($result);
        $id = $result->{$this->keyProperty};
        $type = '0'; // ST_JOURNAL

        $this->assertNotNull($id);

        // List again
        $result = $this->getAll();
        $count1 = count($result);
        $this->assertEquals($count0 + 1, $count1);

        // Get by id
        $response = $client->get($this->url . $type . '/' . $id, array(
            'headers' => TestEnvironment::headers()
        ));
        $this->assertEquals('200', $response->getStatusCode());
        $result = $response->getBody();
        $result = json_decode($result);
        $this->assertEquals($id, $result->{$this->keyProperty});
        $this->checkGetAfterPost($result);

        // Write back
        $response = $client->put($this->url . $id, array(
            'headers' => TestEnvironment::headers(),
            $this->method => $this->putData
        ));

        $this->assertEquals('200', $response->getStatusCode());
        $result = $response->getBody();
        $result = json_decode($result);

        // List again
        // Count should be the same, i.e. no increase
        $result = $this->getAll();
        $count1 = count($result);
        $this->assertEquals($count0 + 1, $count1);

        // Get by id
        $response = $client->get($this->url . $type . '/' . $id, array(
            'headers' => TestEnvironment::headers()
        ));
        $this->assertEquals('200', $response->getStatusCode());
        $result = $response->getBody();
        $result = json_decode($result);
        $this->assertEquals($id, $result->{$this->keyProperty});
        $this->checkGetAfterPut($result);

        // Delete
        $response = $client->delete($this->url . $type . '/' . $id, array(
            'headers' => TestEnvironment::headers()
        ));
        $this->assertEquals('200', $response->getStatusCode());
        $result = $response->getBody();
        $result = json_decode($result);

        // List again
        $result = $this->getAll();
        $count2 = count($result);
        $this->assertEquals($count0, $count2);
    }

}
