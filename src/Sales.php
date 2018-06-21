<?php
namespace FAAPI;

/**
 * @SWG\Definition(
 *   definition="Sale",
 *   type="object",
 *   format="",
 *   description="A Sale",
 *   @SWG\Property(
 *     property="id",
 *     type="integer",
 *     description="Unique id used to reference a Sale",
 *     example="1"
 *   )
 * )
 */
class Sales
{
    // Get Items
    /**
     * @SWG\Get(
     *     path="/sales",
     *     summary="List Sales",
     *     tags={"sales"},
     *     operationId="getSales",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(
     *             type="object",
     *             ref="#/definitions/Sale"
     *         )
     *     ),
     *     deprecated=false
     * )
     */
    public function get($rest, $trans_type)
    {
        $req = $rest->request();
        include_once(API_ROOT . "/sales.inc");

        $page = $req->get("page");

        if ($page == null) {
            sales_all($trans_type);
        } else {
            // If page = 1 the value will be 0, if page = 2 the value will be 1, ...
            $from = -- $page * RESULTS_PER_PAGE;
            sales_all($trans_type, $from);
        }
    }

    // Get Specific Item by Sale Id
    /**
     * @SWG\Get(
     *     path="/sales/id",
     *     summary="Fetch Sale by id",
     *     tags={"sales"},
     *     operationId="getSale",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(
     *             type="object",
     *             ref="#/definitions/Sale"
     *         )
     *     ),
     *     deprecated=false
     * )
     */
    public function getById($rest, $trans_no, $trans_type)
    {
        include_once(API_ROOT . "/sales.inc");
        sales_get($trans_no, $trans_type);
    }
    // Add Item
    /**
     * @SWG\Post(
     *     path="/sales",
     *     summary="Add Sale",
     *     tags={"sales"},
     *     operationId="addSale",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(
     *             type="object",
     *             ref="#/definitions/Sale"
     *         )
     *     ),
     *     deprecated=false
     * )
     */
    public function post($rest)
    {
        include_once(API_ROOT . "/sales.inc");
        sales_add();
    }
    // Edit Specific Item
    /**
     * @SWG\Put(
     *     path="/sales",
     *     summary="Update Sale",
     *     tags={"sales"},
     *     operationId="addSale",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(
     *             type="object",
     *             ref="#/definitions/Sale"
     *         )
     *     ),
     *     deprecated=false
     * )
     */
    public function put($rest, $trans_no, $trans_type)
    {
        include_once(API_ROOT . "/sales.inc");
        sales_edit($trans_no, $trans_type);
    }
    // Delete Specific Item
    public function delete($rest, $branch_id, $uuid)
    {
        include_once(API_ROOT . "/sales.inc");
        sales_cancel($branch_id, $uuid);
    }
}
