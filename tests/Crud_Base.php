<?php

use GuzzleHttp\Client;

require_once(__DIR__ . '/TestConfig.php');

require_once(TEST_PATH . '/TestEnvironment.php');

abstract class Crud_Base extends PHPUnit_Framework_TestCase
{
	private $postData;

	private $putData;

	private $getData;

	private $url;

	private $keyProperty;

	protected $method;

	const JSON = 'json';
	const FORM_DATA = 'form_params';

	/**
	 * Constructor. The $putData and $getData will be set to $postData if not set
	 * @param string $url url of the endpoint 
	 * @param string $keyProperty the property containing the key id
	 * @param array $postData example data to use in post (create)
	 * @param array $putData example data to use in put (update)
	 * @param array $getData example data to compare with in get (read)
	 */
	public function __construct($url, $keyProperty, $postData, $putData = null, $getData = null)
	{
		$this->url = $url;
		$this->keyProperty = $keyProperty;

		$this->postData = $postData;
		$this->putData = $putData ? $putData : $postData;
		$this->getData = $getData ? $getData : $postData;

		$this->method = Crud_Base::FORM_DATA;
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
			$expectedNew = $this->postData;
		}
		return $expectedNew;
	}

	protected function removeKeyProperty($result)
	{
		if (isset($result->{$this->keyProperty}))
		{
			unset($result->{$this->keyProperty});
		}
		return $result;
	}

	protected function checkGetAfterPost($result)
	{
		$expected = $this->fixExpectedType($this->postData, $result);
		$result = $this->removeKeyProperty($result);
		$this->assertEquals($expected, $result, 'Failed GET after POST');
	}

	protected function checkGetAfterPut($result)
	{
		$expected = $this->fixExpectedType($this->putData, $result);
		$result = $this->removeKeyProperty($result);
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

		$this->assertNotNull($id);

		// List again
		$result = $this->getAll();
		$count1 = count($result);
		$this->assertEquals($count0 + 1, $count1);

		// Get by id
		$response = $client->get($this->url . $id, array(
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
		// The id should be the same
		$this->assertEquals($id, $result[$count1 - 1]->{$this->keyProperty});

		// Get by id
		$response = $client->get($this->url . $id, array(
			'headers' => TestEnvironment::headers()
		));
		$this->assertEquals('200', $response->getStatusCode());
		$result = $response->getBody();
		$result = json_decode($result);
		$this->assertEquals($id, $result->{$this->keyProperty});
		$this->checkGetAfterPut($result);

		// Delete
		$response = $client->delete($this->url . $id, array(
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
