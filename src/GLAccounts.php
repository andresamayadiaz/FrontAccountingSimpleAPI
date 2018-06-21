<?php
namespace FAAPI;

$path_to_root = "../..";

include_once($path_to_root . "/gl/includes/db/gl_db_accounts.inc");
include_once($path_to_root . "/gl/includes/db/gl_db_account_types.inc");

/**
 * @SWG\Definition(
 *   definition="GLAccount",
 *   type="object",
 *   format="",
 *   description="A GLAccount",
 *   @SWG\Property(
 *     property="account_code",
 *     type="string",
 *     description="Unique short human readable id used to reference a GLAccount",
 *     example="1060"
 *   ),
 *   @SWG\Property(
 *     property="account_code2",
 *     type="string",
 *     description="Secondary account code, may be blank",
 *     example=""
 *   ),
 *   @SWG\Property(
 *     property="account_name",
 *     type="string",
 *     description="A longer name for the account",
 *     example="My Bank Savings Account"
 *   ),
 *   @SWG\Property(
 *     property="account_type",
 *     type="string",
 *     description="Type of the account",
 *     example=""
 *   ),
 *   @SWG\Property(
 *     property="inactive",
 *     type="int",
 *     description="Zero if account is active",
 *     example="0"
 *   )
 * )
 */
class GLAccounts
{
    /**
     * @SWG\Get(
     *   path="/glaccounts",
     *   summary="List all GL Accounts",
     *   tags={"glaccounts"},
     *   operationId="listGLAccounts",
     *   produces={"application/json"},
     *   @SWG\Response(
     *     response=200,
     *     description="successful operation",
     *     @SWG\Schema(
     *       type="array",
     *       @SWG\Items(ref="#/definitions/GLAccount")
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
            $this->glaccounts_all();
        } else {
            // If page = 1 the value will be 0, if page = 2 the value will be 1, ...
            $from = -- $page * RESULTS_PER_PAGE;
            $this->glaccounts_all($from);
        }
    }

    /**
     * @SWG\Get(
     *   path="/glaccounts/{id}",  
     *   summary="Get GL Account by id",
     *   tags={"glaccounts"},
     *   operationId="getGLAccount",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     description="ID of GL Account to return",
     *     in="path",
     *     name="glAccountId",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="successful operation",
     *     @SWG\Schema(
     *       type="object",
     *       ref="#/definitions/GLAccount"
     *     )
     *   ),
     *   deprecated=false
     * )
     */
    public function getById($rest, $id)
    {
        $result = get_gl_account($id);
        api_success_response(\api_ensureAssociativeArray($result));
    }

    /**
     * @SWG\Post(
     *   path="/glaccounts",
     *   summary="Add GL Account",
     *   tags={"glaccounts"},
     *   operationId="addGLAccount",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="GL Account to be added",
     *     required=true,
     *     @SWG\Schema(ref="#/definitions/GLAccount"),
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
        \api_validate('account_name', $model);
        \api_validate('account_type', $model);
        \api_check('account_code2', $model);
        \api_check('inactive', $model, '0');

        // add_gl_account($account_code, $account_name, $account_type, $account_code2)
        add_gl_account($model['account_code'], $model['account_name'], $model['account_type'], $model['account_code2']);
        // TODO Read back to check before returning success

        \api_create_response(array('account_code' => $model['account_code']));
    }

    /**
     * @SWG\Put(
     *   path="/glaccounts/{id}",
     *   summary="Update GL Account",
     *   tags={"glaccounts"},
     *   operationId="updateGLAccount",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     description="ID of GL Account to update",
     *     in="path",
     *     name="glAccountId",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="GL Account to be updated",
     *     required=true,
     *     @SWG\Schema(ref="#/definitions/GLAccount"),
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
        \api_validate('account_code', $model);
        \api_validate('account_name', $model);
        \api_validate('account_type', $model);
        \api_check('account_code2', $model);
        \api_check('inactive', $model, '0');

        // update_gl_account($account_code, $account_name, $account_type, $account_code2)
        update_gl_account($model['account_code'], $model['account_name'], $model['account_type'], $model['account_code2']);
        // TODO Read back to check before returning success

        \api_success_response(array('account_code' => $id));
    }

    /**
     * @SWG\Delete(
     *   path="/glaccounts/{id}",
     *   summary="Delete GL Account",
     *   tags={"glaccounts"},
     *   operationId="deleteGLAccount",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     description="ID of GL Account to delete",
     *     in="path",
     *     name="glAccountId",
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

        delete_gl_account($id);

        \api_success_response(array('msg' => 'deleted', 'id' => $id));
    }

    // TODO Docs CP 2018-06
    public function getTypes($rest)
    {
        $accttypes = get_account_types();
        $ret = array();
        while ($type = db_fetch($accttypes)) {
            $ret[] = array(
                'id' => $type['id'],
                'name' => $type['name'],
                'class_id' => $type['class_id'],
                'parent' => $type['parent']
            );
        }
        api_success_response(json_encode($ret));
    }

    private function glaccounts_all($from = null)
    {
        if ($from == null) {
            $from = 0;
        }

        // TODO Paging doesn't work CP 2018-06
        // $sql = "SELECT " . TB_PREF . "chart_master.*," . TB_PREF . "chart_types.name AS AccountTypeName FROM " . TB_PREF . "chart_master," . TB_PREF . "chart_types WHERE " . TB_PREF . "chart_master.account_type=" . TB_PREF . "chart_types.id ORDER BY account_code LIMIT " . $from . ", " . RESULTS_PER_PAGE;
        $sql = "SELECT " . TB_PREF . "chart_master.*," . TB_PREF . "chart_types.name AS AccountTypeName FROM " . TB_PREF . "chart_master," . TB_PREF . "chart_types WHERE " . TB_PREF . "chart_master.account_type=" . TB_PREF . "chart_types.id ORDER BY account_code";
        $query = db_query($sql, "error");
        $info = array();
        while ($data = db_fetch($query, "error")) {
            $info[] = array(
                'account_code' => $data['account_code'],
                'account_name' => $data['account_name'],
                'account_type' => $data['account_type'],
                'account_code2' => $data['account_code2']
            );
        }

        api_success_response(json_encode($info));
    }
}
