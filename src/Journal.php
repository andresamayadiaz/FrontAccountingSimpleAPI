<?php
namespace FAAPI;

$path_to_root = "../..";

include_once($path_to_root . "/includes/ui/items_cart.inc");
include_once($path_to_root . "/gl/includes/db/gl_journal.inc");

/**
 * @SWG\Definition(
 *   definition="JournalEntry",
 *   type="object",
 *   format="",
 *   description="A single Journal Entry",
 *   @SWG\Property(
 *     property="id", type="integer", example="1",
 *     description="Unique id used to reference a Journal Entry"
 *   ),
 *   @SWG\Property(
 *     property="type", type="integer", example="10",
 *     description="Transaction type is the type of document this Journal Entry represents, e.g. Invoice, Delivery, GRN etc."
 *   ),
 *   @SWG\Property(
 *     property="journal_date", type="date", example="2018-06-21",
 *     description="Date of Journal Entry"
 *   ),
 *   @SWG\Property(
 *     property="document_date", type="date", example="2018-06-21",
 *     description="Date of related document"
 *   ),
 *   @SWG\Property(
 *     property="event_date", type="date", example="2018-06-21",
 *     description="Date of related event"
 *   ),
 *   @SWG\Property(
 *     property="currency", type="string", example="USD",
 *     description="Currency "
 *   ),
 *   @SWG\Property(
 *     property="document_ref", type="string", example="INV123456",
 *     description="Reference of the related document"
 *   ),
 *   @SWG\Property(
 *     property="reference", type="string", example="18 001", required="false",
 *     description="Reference for this Journal Entry"
 *   )
 * )
 */
class Journal
{
    /**
     * @SWG\Get(
     *   path="/journal",
     *   summary="List Journal Entries",
     *   tags={"journal"},
     *   operationId="listJournalEntries",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     description="filter start date",
     *     in="path",
     *     name="from",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     description="filter end date",
     *     in="path",
     *     name="to",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     description="reference to filter by",
     *     in="path",
     *     name="ref",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="successful operation",
     *     @SWG\Schema(
     *       type="array",
     *       @SWG\Items(ref="#/definitions/JournalEntry")
     *     )
     *   ),
     *   deprecated=false
     * )
     */
    public function get($rest)
    {
        $req = $rest->request();
        $from = $req->get("from");
        $to = $req->get("to");
        $ref = $req->get("ref");
        if (!$from) {
            $from = begin_fiscalyear();
        }
        if (!$to) {
            $to = end_fiscalyear();
        }
        
        $filter = -1;
        $memo = '';
        $alsoClosed = false;
        $userId = null;
        $contractorId = null;
        $dimension = null;
        $sql = get_sql_for_journal_inquiry(
            // The following are mandatory
            $filter, $from, $to,
            // The following are optonal
            $ref, $memo, $alsoClosed, $userId, $contractorId, $dimension
        );
        $result = db_query($sql, "error");
        $info = array();
        while ($result && $data = db_fetch_assoc($result, "error")) {
            $info[] = $data;
        }

        api_success_response($info);
    }

    /**
     * @SWG\Get(
     *   path="/journal/{type}/{id}",
     *   summary="Get Journal Entry by type and id",
     *   tags={"journal"},
     *   operationId="getJournalEntry",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     description="Type of Journal Entry to return",
     *     in="path",
     *     name="journalType",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     description="ID of Journal Entry to return",
     *     in="path",
     *     name="journalId",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="successful operation",
     *     @SWG\Schema(
     *       type="object",
     *       ref="#/definitions/JournalEntry"
     *     )
     *   ),
     *   deprecated=false
     * )
     */
    public function getById($rest, $type, $id)
    {
        $info = array();
        if ($type == ST_JOURNAL) {
            $journal = get_journal($type, $id);
            $info['document_date'] = sql2date($journal['doc_date']);
            $info['event_date'] = sql2date($journal['event_date']);
            $info['document_ref'] = $journal['source_ref'];
            $info['currency'] = $journal['currency'];
            $info['trans_date'] = sql2date($journal['tran_date']);
        } else {
            $info['counterparty'] = get_counterparty_name($type, $id);
        }
        // journal number ??? CP 2018-06
        $items = array();
        $result = get_gl_trans($type, $id);
        $i = 0;
        while ($row = db_fetch_assoc($result)) {
            if ($i == 0) {
                if (!$info['trans_date']) {
                    $info['trans_date'] = sql2date($row['tran_date']);
                }
                $info['reference'] = $row['reference'];
            }
            $items[] = array(
                'account_code' => $row['account'],
                'amount' => $row['amount'],
                'memo' => $row['memo_']
            );
            $i++;
        }
        if ($items === array()) {
            \api_error(404, "Journal Entry not found");
        }
        $info['items'] = $items;
        $info['type'] = $type;
        $info['id'] = $id;
        // error_log(var_export($info, true), 4);
        api_success_response($info);
    }

    /**
     * @SWG\Post(
     *   path="/journal",
     *   summary="Add Journal Entry",
     *   tags={"journal"},
     *   operationId="addJournalEntry",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Journal Entry to be added",
     *     required=true,
     *     @SWG\Schema(ref="#/definitions/JournalEntry"),
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
        global $Refs;
        $req = $rest->request();
        $model = $req->post();

        $today = new_doc_date();

        \api_validate('items', $model, 412, function($property, $model) {
            return is_array($model[$property]);
        }, 'items must be an array');
        \api_check('currency', $model);
        \api_check('trans_date', $model, $today);
        \api_check('document_date', $model, $today);
        \api_check('event_date', $model, $today);
        \api_check('document_ref', $model);
        \api_check('reference', $model);
        \api_check('memo', $model);
        foreach ($model['items'] as $item) {
            \api_validate('account_code', $item);
            \api_validate('amount', $item);
            \api_check('memo', $item);
        }
        $cart = new \items_cart(ST_JOURNAL);
        $cart->tran_date = $model['trans_date'];
        $cart->doc_date = $model['document_date'];
        $cart->event_date = $model['event_date'];
        $cart->source_ref = $model['document_ref'];
        if (!$model['currency']) {
            $cart->currency = $model['currency'];
        }
        // TODO check fiscal year
        if (!$model['reference']) {
            $cart->reference = $Refs->get_next(ST_JOURNAL, null, $cart->tran_date);
        }
        foreach ($model['items'] as $item) {
            $cart->add_gl_item($item['account_code'], '', '', $item['amount'], $item['memo']);
        }

        $id = write_journal_entries($cart);

        \api_create_response(array('id' => $id));
    }

    /**
     * @SWG\Put(
     *   path="/journal/{id}",
     *   summary="Update Journal Entry",
     *   tags={"journal"},
     *   operationId="updateJournalEntry",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     description="ID of Journal Entry to update",
     *     in="path",
     *     name="journalId",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Journal Entry to be updated",
     *     required=true,
     *     @SWG\Schema(ref="#/definitions/JournalEntry"),
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
        /* IMPORTANT NOTE: This function only updates the journal table.
         * It does not void the transaction and then re-post
         */
        $req = $rest->request();
        $model = $req->post();

        $existing = $this->getById($rest, ST_JOURNAL, $id);

        $today = new_doc_date();

        \api_validate('items', $model, 412, function($property, $model) {
            return !\array_key_exists($property, $model);
        }, 'This api cannot update journal items');
        \api_validate('currency', $model, 412, function($property, $model) {
            return !\array_key_exists($property, $model);
        }, 'This api cannot update currency');
        \api_validate('reference', $model, 412, function($property, $model) {
            return !\array_key_exists($property, $model);
        }, 'This api cannot update reference');
        \api_check('trans_date', $model, $existing);
        \api_check('document_date', $model, $existing);
        \api_check('event_date', $model, $existing);
        \api_check('document_ref', $model, $existing);
        \api_check('memo', $model, $existing);

        // !!! We need to ensure that $amount does not change
        $sql = "UPDATE ".TB_PREF."journal SET "
        ."`source_ref`=".db_escape($model['document_ref']).","
        ."`tran_date`="."'".date2sql($model['trans_date'])."',"
        ."`event_date`="."'".date2sql($model['event_date'])."',"
        ."`doc_date`="."'".date2sql($model['document_date'])."'"
        ." WHERE `type`=".db_escape(ST_JOURNAL)." AND " ."`trans_no`=".db_escape($id);
  
        db_query($sql, 'cannot update journal entry');

        \api_success_response(array('id' => $id));
    }

    /**
     * @SWG\Delete(
     *   path="/journal/{type}/{id}",
     *   summary="Void Journal Entry",
     *   tags={"journal"},
     *   operationId="voidJournalEntry",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     description="Type of Journal Entry to void",
     *     in="path",
     *     name="journalType",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     description="ID of Journal Entry to void",
     *     in="path",
     *     name="journalId",
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
    public function delete($rest, $type, $id)
    {
        $existing = $this->getById($rest, $type, $id);

        $msg = void_transaction($type, $id, Today(), _("Document void by api."));

        \api_success_response(array('msg' => 'voided', 'id' => $id));
    }

}
