<?php

use GuzzleHttp\Client;

require_once(__DIR__ . '/TestConfig.php');

require_once(TEST_PATH . '/TestEnvironment.php');

/**
 * @group wip
 */
class GLTrialBalanceTest extends PHPUnit_Framework_TestCase
{
    public function testAccountTypes_Ok()
    {
        $client = TestEnvironment::client();
        // Post a test journal so that we can see some output in the trial balance.
        $testId = TestEnvironment::createJournal($client, '2013-02-03', array(
            array(
                'account_code' => '1060',
                'amount' => '11.00'
            ),
            array(
                'account_code' => '4010',
                'amount' => '-11.00'
            )
        ));

        $response = $client->get('/modules/api/glquery/trialbalance/2013-01-01/2013-12-31/?nozeros', array(
            'headers' => TestEnvironment::headers()
        ));

        $this->assertEquals('200', $response->getStatusCode());
        $result = $response->getBody();
        $result = json_decode($result);

        // var_dump($result);

        $this->assertEquals('2013-01-01', $result->start);
        $this->assertEquals('2013-12-31', $result->end);
        $this->assertEquals(true, $result->options->nozeros);
        $expected = new stdClass();
        $expected->account_name = 'Checking Account';
        $expected->start_debit = 0;
        $expected->start_credit = 0;
        $expected->period_debit = 11;
        $expected->period_credit = 0;
        $expected->end_debit = 11;
        $expected->end_credit = 0;
        $this->assertEquals($expected, $result->accounts->{'1060'});
        $expected = new stdClass();
        $expected->account_name = 'Sales';
        $expected->start_debit = 0;
        $expected->start_credit = 0;
        $expected->period_debit = 0;
        $expected->period_credit = 11;
        $expected->end_debit = 0;
        $expected->end_credit = 11;
        $this->assertEquals($expected, $result->accounts->{'4010'});

        TestEnvironment::deleteJournal($client, $testId);
    }
}
