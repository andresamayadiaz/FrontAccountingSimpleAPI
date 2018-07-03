<?php
namespace FAAPI;

$path_to_root = "../..";

include_once($path_to_root . "/admin/db/fiscalyears_db.inc");
include_once($path_to_root . "/gl/includes/db/gl_db_accounts.inc");
include_once($path_to_root . "/gl/includes/db/gl_db_trans.inc");

class GLQueries
{
    /**
     * @SWG\Definition(
     *   definition="GLTrialBalanceLine",
     *   type="object",
     *   format="",
     *   description="A GLTrialBalance Line Entry",
     *   @SWG\Property(property="account_code", type="string", example="1060",
     *     description="Unique short human readable id used to reference a GLAccount"
     *   ),
     *   @SWG\Property(property="account_name", type="string", example="My Bank Savings Account",
     *     description="A longer name for the account"
     *   ),
     *   @SWG\Property(property="start_debit", type="float", example="10.00",
     *     description="Opening debit balance at the start of the period"
     *   ),
     *   @SWG\Property(property="start_credit", type="float", example="10.00",
     *     description="Opening credit balance at the start of the period"
     *   ),
     *   @SWG\Property(property="period_debit", type="float", example="10.00",
     *     description="Period debit balance"
     *   ),
     *   @SWG\Property(property="period_credit", type="float", example="10.00",
     *     description="Period credit balance"
     *   ),
     *   @SWG\Property(property="end_debit", type="float", example="10.00",
     *     description="Closing debit balance at the end of the period"
     *   ),
     *   @SWG\Property(property="end_credit", type="float", example="10.00",
     *     description="Closing credit balance at the end of the period"
     *   ),
     * )
     */
    /**
     * @SWG\Get(
     *   path="/glqueries/trialbalance/{start}/{end}/",
     *   summary="Query the GL Trial Balance",
     *   tags={"glquery/trialbalance"},
     *   operationId="queryTrialBalance",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     description="Start date of the period",
     *     in="path",
     *     name="start",
     *     required=true,
     *     type="date"
     *   ),
     *   @SWG\Parameter(
     *     description="End date of the period",
     *     in="path",
     *     name="end",
     *     required=true,
     *     type="date"
     *   ),
     *   @SWG\Parameter(
     *     description="Dimension 1",
     *     in="path",
     *     name="dimension1",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     description="Dimension 2",
     *     in="path",
     *     name="dimension2",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     description="Present to ommit accounts with zero balances from the result",
     *     in="path",
     *     name="nozeros",
     *     required=false,
     *     type="bool"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="successful operation",
     *     @SWG\Schema(
     *       type="array",
     *       @SWG\Items(ref="#/definitions/GLTrialBalanceLine")
     *     )
     *   ),
     *   deprecated=false
     * )
     */
    public function trialBalance($rest, $start, $end)
    {
        $req = $rest->request();
        \api_validate('start', $start, 412, 'api_validate_date');
        \api_validate('end', $end, 412, 'api_validate_date');
        $options = array(
            'dimension1' => $req->get('dimension1'),
            'dimension2' => $req->get('dimension2'),
            'nozeros' => $req->get('nozeros') !== null
        );
        \api_check('dimension1', $options);
        \api_check('dimension2', $options);
        \api_check('nozeros', $options, false);

        $startDate = get_fiscalyear_begin_for_date(sql2date($start));

        $result = array(
            'start' => $start,
            'end' => $end,
            'accounts' => array()
        );
        foreach (\array_keys($options) as $option) {
            if ($options[$option]) {
                $result['options'][$option] = $options[$option];
            }
        }
        $accounts = get_gl_accounts();
        while ($account = db_fetch_assoc($accounts)) {
            $prev = get_balance($account["account_code"], $options['dimension1'], $options['dimension2'], $startDate, sql2date($start), false, false);
            $curr = get_balance($account["account_code"], $options['dimension1'], $options['dimension2'], sql2date($start), sql2date($end), true, true);
            $tot = get_balance($account["account_code"], $options['dimension1'], $options['dimension2'], $startDate, sql2date($end), false, true);
            if ($options['nozeros'] && !$prev['balance'] && !$curr['balance'] && !$tot['balance']) {
                continue;
            }
            $result['accounts'][$account['account_code']] = array(
                'account_name'  => $account['account_name'],
                'start_debit'   => round($prev['debit'], 2),
                'start_credit'  => round($prev['credit'], 2),
                'period_debit'  => round($curr['debit'], 2),
                'period_credit' => round($curr['credit'], 2),
                'end_debit'     => round($tot['debit'], 2),
                'end_credit'    => round($tot['credit'], 2)
            );
        }
        return \api_success_response($result);
    }
}
