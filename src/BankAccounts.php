<?php
namespace FAAPI;

$path_to_root = "../..";

include_once($path_to_root . "/gl/includes/db/gl_db_bank_accounts.inc");

/**
 * @SWG\Definition(
 *   definition="BankAccount",
 *   type="object",
 *   format="",
 *   description="A Bank Account",
 *   @SWG\Property(
 *     property="id", type="integer", example="1",
 *     description="Unique id used to reference a Bank Account"
 *   ),
 *   @SWG\Property(
 *     property="account_code", type="string", example="1060",
 *     description="Account GL code"
 *   ),
 *   @SWG\Property(
 *     property="account_type", type="integer", example="0",
 *     description="Type of the account"
 *   ),
 *   @SWG\Property(
 *     property="bank_name", type="string", example="Some Bank",
 *     description="Name of the bank at which this account is held"
 *   ),
 *   @SWG\Property(
 *     property="bank_address", type="string",
 *     description="Address of the Bank"
 *   ),
 *   @SWG\Property(
 *     property="bank_account_name", type="string", example="Anne X Ample",
 *     description="Name of the account"
 *   ),
 *   @SWG\Property(
 *     property="bank_account_number", type="string", example="12-3456-789123-00",
 *     description="Account number used by the Bank"
 *   ),
 *   @SWG\Property(
 *     property="bank_curr_code", type="string", example="USD",
 *     description="Currency of the account"
 *   ),
 *   @SWG\Property(
 *     property="dflt_curr_act", type="boolean", example="0",
 *     description="True (1) if this account is the default account"
 *   ),
 *   @SWG\Property(
 *     property="bank_charge_act", type="string", example="5690",
 *     description="GL account to which bank charges are assigned"
 *   ),
 *   @SWG\Property(
 *     property="last_reconciled_date", type="date", example="2017-12-01",
 *     description="Date up to which this account was reconciled"
 *   ),
 *   @SWG\Property(
 *     property="ending_reconcile_balance", type="number", example="12.34",
 *     description="Account balance at last reconcile"
 *   ),
 *   @SWG\Property(
 *     property="inactive", type="boolean", example="0",
 *     description="True if this account is not active"
 *   )
 * )
 */
class BankAccounts
{
    /**
     * @SWG\Get(
     *   path="/bankaccounts",
     *   summary="List Bank Accounts",
     *   tags={"bankaccounts"},
     *   operationId="listBankAccounts",
     *   produces={"application/json"},
     *   @SWG\Response(
     *     response=200,
     *     description="successful operation",
     *     @SWG\Schema(
     *       type="array",
     *       @SWG\Items(ref="#/definitions/BankAccount")
     *     )
     *   ),
     *   deprecated=false
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
     *   path="/bankaccounts/{id}",
     *   summary="Get Bank Account by id",
     *   tags={"bankaccounts"},
     *   operationId="getBankAccount",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     description="ID of Bank Account to return",
     *     in="path",
     *     name="bankAccountId",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="successful operation",
     *     @SWG\Schema(
     *       type="object",
     *       ref="#/definitions/BankAccount"
     *     )
     *   ),
     *   deprecated=false
     * )
     */
    public function getById($rest, $id)
    {
        $bank = get_bank_account($id);
        api_success_response(json_encode(\api_ensureAssociativeArray($bank)));
    }

    /**
     * @SWG\Post(
     *   path="/bankaccounts",
     *   summary="Add Bank Account",
     *   tags={"bankaccounts"},
     *   operationId="addBankAccount",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Bank Account to be added",
     *     required=true,
     *     @SWG\Schema(ref="#/definitions/BankAccount"),
     *   ),
     *   @SWG\Response(
     *     response=201,
     *     description="successful operation",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="account_code", type="string")
     *     )
     *   ),
     *   deprecated=false
     * )
     */
    public function post($rest)
    {
        $req = $rest->request();
        $model = $req->post();
        \api_validate('account_code', $model);
        \api_validate('account_type', $model);
        \api_validate('bank_account_name', $model);
        \api_validate('bank_account_number', $model);
        \api_validate('bank_curr_code', $model);
        \api_check('bank_name', $model);
        \api_check('bank_address', $model);
        \api_check('dflt_curr_act', $model);
        \api_check('bank_charge_act', $model);
        \api_check('inactive', $model, '0');

        // add_bank_account($account_code, $account_type, $bank_account_name,
        //     $bank_name, $bank_account_number, $bank_address, $bank_curr_code, 
        //     $dflt_curr_act, $bank_charge_act
        // )
        add_bank_account($model['account_code'], $model['account_type'], $model['bank_account_name'],
            $model['bank_name'], $model['bank_account_number'], $model['bank_address'], $model['bank_curr_code'], 
            $model['dflt_curr_act'], $model['bank_charge_act']
        );
        // add_bank_account does not return the newly inserted id, therefore
        // we query based on the account_code which *should* be unique even
        // though the database schema (as of 2.4.4) doesn't enforce this.
        $sql = "SELECT id, account_code FROM " . TB_PREF . "bank_accounts WHERE account_code=" . db_escape($model['account_code']);
        $result = db_query($sql, "Cannot query bank_accounts");
        $count = db_num_rows($result);
        if ($count == 0) {
            \api_error(502, sprintf("Could not add '%s'", db_escape($model['account_code'])));
        }
        if ($count > 1) {
            \api_error(502, sprintf("Multiple accounts exist for '%s'", db_escape($model['account_code'])));
        }
        $row = db_fetch_assoc($result);
        \api_create_response(array('id' => $row['id']));
    }

    /**
     * @SWG\Put(
     *   path="/bankaccounts/{id}",
     *   summary="Update Bank Account",
     *   tags={"bankaccounts"},
     *   operationId="updateBankAccount",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     description="ID of Bank Account to update",
     *     in="path",
     *     name="bankAccountId",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Bank Account to be updated",
     *     required=true,
     *     @SWG\Schema(ref="#/definitions/BankAccount"),
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="successful operation",
     *     @SWG\Schema(
     *       type="object",
     * 	     @SWG\Property(property="account_code", type="string")
     *     )
     *   ),
     *   deprecated=false
     * )
     */
    public function put($rest, $id)
    {
        $req = $rest->request();
        $model = $req->post();

        $existing = get_bank_account($id);

        \api_check('account_code', $model, $existing);
        \api_check('account_type', $model, $existing);
        \api_check('bank_account_name', $model, $existing);
        \api_check('bank_account_number', $model, $existing);
        \api_check('bank_curr_code', $model, $existing);
        \api_check('bank_name', $model, $existing);
        \api_check('bank_address', $model, $existing);
        \api_check('dflt_curr_act', $model, $existing);
        \api_check('bank_charge_act', $model, $existing);
        \api_check('inactive', $model, $existing);

        // TODO: inactive not supported for update CP 2018-06

        // FA 2.4.4 function prototype
        // update_bank_account($id, $account_code, $account_type, $bank_account_name, 
        // $bank_name, $bank_account_number, $bank_address, $bank_curr_code, $dflt_curr_act, $bank_charge_act)
        update_bank_account(
            $id, $model['account_code'], $model['account_type'], $model['bank_account_name'], 
            $model['bank_name'], $model['bank_account_number'], $model['bank_address'], $model['bank_curr_code'], $model['dflt_curr_act'], $model['bank_charge_act']
        );

        \api_success_response(array('id' => $id));
    }

    /**
     * @SWG\Delete(
     *   path="/bankaccounts/{id}",
     *   summary="Delete Bank Account",
     *   tags={"bankaccounts"},
     *   operationId="deleteBankAccount",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     description="ID of Bank Account to delete",
     *     in="path",
     *     name="bankAccountId",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="successful operation",
     *   ),
     *   deprecated=false
     * )
     */
    public function delete($rest, $id)
    {
        $req = $rest->request();
        $info = $req->post();

        delete_bank_account($id);

        \api_success_response(array('msg' => 'deleted', 'id' => $id));
    }

    private function bankaccounts_all($from = null)
    {
        if ($from == null) {
            $from = 0;
        }

        // TODO Paging doesn't work CP 2018-06
        // $sql = "SELECT * FROM " . TB_PREF . "bank_accounts LIMIT " . $from . ", " . RESULTS_PER_PAGE;
        $sql = "SELECT * FROM " . TB_PREF . "bank_accounts";
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
