<?php
namespace FAAPI;

$path_to_root = "../..";

include_once ($path_to_root . "/gl/includes/db/gl_db_bank_accounts.inc");
/**
 * @SWG\Definition(
 *   definition="BankAccount",
 *   type="object",
 *   format="",
 *   description="A Bank Account",
 *   @SWG\Property(
 *     property="id",
 *     type="integer",
 *     description="Unique id used to reference a Bank Account",
 *     example="1"
 *   ),
 *   @SWG\Property(
 *     property="account_code",
 *     type="string",
 *     description="Account GL code",
 *     example="1060"
 *   ),
 *   @SWG\Property(
 *     property="account_type",
 *     type="integer",
 *     description="Type of the account",
 *     example="0"
 *   ),
 *   @SWG\Property(
 *     property="bank_name",
 *     type="string",
 *     description="Name of the bank at which this account is held",
 *     example="Some Bank"
 *   ),
 *   @SWG\Property(
 *     property="bank_address",
 *     type="string",
 *     description="Address of the Bank"
 *   ),
 *   @SWG\Property(
 *     property="bank_account_name",
 *     type="string",
 *     description="Name of the account",
 *     example="Anne X Ample"
 *   ),
 *   @SWG\Property(
 *     property="bank_account_number",
 *     type="string",
 *     description="Account number used by the Bank",
 *     example="12-3456-7890-1"
 *   ),
 *   @SWG\Property(
 *     property="bank_curr_code",
 *     type="string",
 *     description="Currency of the account",
 *     example="USD"
 *   ),
 *   @SWG\Property(
 *     property="dflt_curr_act",
 *     type="boolean",
 *     description="True (1) if this account is the default account",
 *     example="0"
 *   ),
 *   @SWG\Property(
 *     property="bank_charge_act",
 *     type="string",
 *     description="GL account to which bank charges are assigned",
 *     example="5690"
 *   ),
 *   @SWG\Property(
 *     property="last_reconciled_date",
 *     type="date",
 *     description="Date up to which this account was reconciled",
 *     example="2017-12-01"
 *   ),
 *   @SWG\Property(
 *     property="ending_reconcile_balance",
 *     type="number",
 *     description="Account balance at last reconcile",
 *     example="12.34"
 *   ),
 *   @SWG\Property(
 *     property="inactive",
 *     type="boolean",
 *     description="True if this account is not active",
 *     example="0"
 *   )
 * )
 */
class BankAccounts
{
    /**
     * @SWG\Get(
     *     path="/bankaccounts",
     *     summary="List Bank Accounts",
     *     tags={"bank account"},
     *     operationId="listBankAccounts",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(ref="#/definitions/BankAccount")
     *         )
     *     ),
     *     deprecated=false
     * )
     */
    public function get($rest)
    {
        $req = $rest->request();

        $page = $req->get("page");

        if ($page == null) {
            $this->bankaccounts_all();
        } else {
            // If page = 1 the value will be 0, if page = 2 the value will be 1, ...
            $from = -- $page * RESULTS_PER_PAGE;
            $this->bankaccounts_all($from);
        }
    }

    /**
     * @SWG\Get(
     *     path="/bankaccounts/id",
     *     summary="Fetch Bank Account by id",
     *     tags={"bank account"},
     *     operationId="getBankAccount",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(
     *             type="object",
     *             ref="#/definitions/BankAccount"
     *         )
     *     ),
     *     deprecated=false
     * )
     */
    public function getById($rest, $id)
    {
        $bank = get_bank_account($id);
        api_success_response(json_encode(\api_ensureAssociativeArray($bank)));
    }

    private function bankaccounts_all($from = null)
    {
        if ($from == null) {
            $from = 0;
        }
        $sql = "SELECT * FROM " . TB_PREF . "bank_accounts LIMIT " . $from . ", " . RESULTS_PER_PAGE;
        $query = db_query($sql, "error");

        $info = array();

        while ($data = db_fetch($query, "error")) {
            $info[] = array(
                "id" => $data["id"],
                "account_type" => $data["account_type"],
                "account_code" => $data["account_code"],
                "bank_account_name" => $data["bank_account_name"],
                "bank_name" => $data["bank_name"],
                "bank_account_number" => $data["bank_account_number"],
                "bank_curr_code" => $data["bank_curr_code"],
                "bank_address" => $data["bank_address"],
                "dflt_curr_act" => $data["dflt_curr_act"]
            );
        }

        api_success_response(json_encode($info));
    }
}
