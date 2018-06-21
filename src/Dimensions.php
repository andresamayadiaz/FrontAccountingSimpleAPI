<?php
namespace FAAPI;

$path_to_root = "../..";

include_once($path_to_root . "/dimensions/includes/dimensions_db.inc");

/**
 * @SWG\Definition(
 *   definition="Dimension",
 *   type="object",
 *   format="",
 *   description="A Dimension",
 *   @SWG\Property(
 *     property="id",
 *     type="integer",
 *     description="Unique id used to reference a Dimension",
 *     example="1"
 *   ),
 *   @SWG\Property(
 *     property="reference",
 *     type="string",
 *     description="Unique short human readable reference",
 *     example="PROJECT1"
 *   ),
 *   @SWG\Property(
 *     property="name",
 *     type="string",
 *     description="A longer human readable name",
 *     example="Project 1: Building buildings"
 *   ),
 *   @SWG\Property(
 *     property="memo",
 *     type="string",
 *     description="A longer memo",
 *     example="Some memo"
 *   )
 * )
 */
class Dimensions
{
    /**
     * @SWG\Get(
     *   path="/dimensions",
     *   summary="List all Dimensions",
     *   tags={"dimensions"},
     *   operationId="listDimensions",
     *   produces={"application/json"},
     *   @SWG\Response(
     *     response=200,
     *     description="successful operation",
     *     @SWG\Schema(
     *       type="array",
     *       @SWG\Items(ref="#/definitions/Dimension")
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
            $this->all();
        } else {
            // If page = 1 the value will be 0, if page = 2 the value will be 1, ...
            $from = -- $page * RESULTS_PER_PAGE;
            $this->all($from);
        }
    }

    /**
     * @SWG\Get(
     *   path="/dimensions/{id}",
     *   summary="Get Dimension by id",
     *   tags={"dimensions"},
     *   operationId="getDimension",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     description="ID of Dimension to return",
     *     in="path",
     *     name="dimensionId",
     *     required=true,
     *     type="integer",
     *     format="int64"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="successful operation",
     *     @SWG\Schema(
     *       type="object",
     *       ref="#/definitions/Dimension"
     *     )
     *   ),
     *   deprecated=false
     * )
     */
    public function getById($rest, $id)
    {
        $result = get_dimension($id);
        api_success_response(api_ensureAssociativeArray($result));
    }

    /**
     * @SWG\Post(
     *   path="/dimensions",
     *   summary="Add Dimension",
     *   tags={"dimensions"},
     *   operationId="addDimension",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Dimension to be added",
     *     required=true,
     *     @SWG\Schema(ref="#/definitions/Dimension"),
     *   ),
     *   @SWG\Response(
     *     response=201,
     *     description="successful operation",
     *     @SWG\Schema(
     *       type="object",
     * 	     @SWG\Property(property="id", type="string")
     *     )
     *   ),
     *   deprecated=false
     * )
     */
    public function post($rest)
    {
        $req = $rest->request();
        $model = $req->post();
        \api_validate('reference', $model);
        \api_validate('name', $model);
        \api_check('memo', $model);
        // add_dimension($reference, $name, $type_, $date_, $due_date, $memo_)
        $id = add_dimension($model['reference'], $model['name'], '', '', '', $model['memo']);
        \api_create_response(array('id' => $id));
    }

    /**
     * @SWG\Put(
     *   path="/dimensions/{id}",
     *   summary="Update Dimension",
     *   tags={"dimensions"},
     *   operationId="updateDimension",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     description="ID of Dimension to update",
     *     in="path",
     *     name="dimensionId",
     *     required=true,
     *     type="integer",
     *     format="int64"
     *   ),
     *   @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Dimension to be updated",
     *     required=true,
     *     @SWG\Schema(ref="#/definitions/Dimension"),
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="successful operation",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Property(property="id", type="string")
     *     )
     *   ),
     *   deprecated=false
     * )
     */
    public function put($rest, $id)
    {
        $req = $rest->request();
        $model = $req->post();
        \api_validate('reference', $model);
        \api_validate('name', $model);
        \api_check('memo', $model);
        // update_dimension($id, $name, $type_, $date_, $due_date, $memo_)
        $id = update_dimension($id, $model['name'], '', '', '', $model['memo']);
        \api_success_response(array('id' => $id));
    }

    /**
     * @SWG\Delete(
     *   path="/dimensions/{id}",
     *   summary="Delete Dimension",
     *   tags={"dimensions"},
     *   operationId="deleteDimension",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     description="ID of Dimension to delete",
     *     in="path",
     *     name="dimensionId",
     *     required=true,
     *     type="integer",
     *     format="int64"
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
        delete_dimension($id);
        \api_success_response(array('msg' => 'deleted', 'id' => $id));
    }

    private function all($from = null)
    {
        $dbResult = get_dimensions();
        $result = array();
        while ($row = db_fetch_assoc($dbResult)) {
            $result[] = $row;
        }
        \api_success_response($result);

        // if ($from == null)
        // 	$from = 0;

        // $sql = "SELECT * FROM " . TB_PREF . "debtors_master WHERE !inactive LIMIT " . $from . ", " . RESULTS_PER_PAGE;

        // $query = db_query($sql, "error");

        // $info = array();

        // while ($data = db_fetch_assoc($query, "error")) {
        // 	$info[] = $data;
        // }

        // api_success_response(json_encode($info));
    }
}
