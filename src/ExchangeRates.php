<?php
namespace FAAPI;

$path_to_root = "../..";

include_once($path_to_root . "/gl/includes/db/gl_db_rates.inc");

/**
 * @SWG\Definition(
 *   definition="ExchangeRate",
 *   type="object",
 *   format="",
 *   description="An ExchangeRate",
 *   @SWG\Property(
 *     property="id",
 *     type="integer",
 *     description="Unique id used to reference an ExchangeRate",
 *     example="1"
 *   ),
 *   @SWG\Property(
 *     property="currency",
 *     type="string",
 *     description="Standard currency code",
 *     example="USD"
 *   ),
 *   @SWG\Property(
 *     property="rateBuy",
 *     type="float",
 *     description="The 'buy' exchange rate",
 *     example="1.12"
 *   ),
 *   @SWG\Property(
 *     property="rateSell",
 *     type="float",
 *     description="The 'sell' exchange rate, if not set the 'rateBuy' is used",
 *     example="1.12"
 *   ),
 *   @SWG\Property(
 *     property="date",
 *     type="date",
 *     description="The effective date from which to use this exchange rate",
 *     example="2018-06-26"
 *   )
 * )
 */
class ExchangeRates
{
    /* Note that the callable syntax used when using these functions is
     * intensionally using the array style rather than the string style.
     * i.e.
     *   \api_validate(null, $currency, 412, array(self::class, 'validateCurrencyExists'), ...
     * rather than
     *   \api_validate(null, $currency, 412, '\FAAPI\ExchanageRates::validateCurrencyExists', ...
     * which fails in php 5.6 but passes in php 7.0
     * see https://travis-ci.org/cambell-prince/FrontAccountingSimpleAPI/builds/396900851
     */
    public static function validateCurrencyExists($property, $model)
    {
        $currency = $property ? $model[$property] : $model;
        return false !== get_currency($currency);
    }

    public static function validateNotCompanyCurrency($property, $model)
    {
        $currency = $property ? $model[$property] : $model;
        return false === is_company_currency($currency);
    }

    /**
     * @SWG\Get(
     *   path="/exchangerates/{currency}",
     *   summary="List all ExchangeRates for a given currency",
     *   tags={"exchangerates"},
     *   operationId="listExchangeRates",
     *   produces={"application/json"},
     *   @SWG\Response(
     *     response=200,
     *     description="successful operation",
     *     @SWG\Schema(
     *       type="array",
     *       @SWG\Items(ref="#/definitions/ExchangeRate")
     *     )
     *   ),
     *   @SWG\Response(
     *     response=412,
     *     description="failed precondition",
     *     @SWG\Schema(
     *       type="object",
     *       @SWG\Items(ref="#/definitions/Error")
     *     )
     *   ),
     *   deprecated=false
     * )
     */
    public function getAllByCurrency($rest, $currency)
    {
        $req = $rest->request();
        $currency = \strtoupper($currency);

        // Validation
        \api_validate(null, $currency, 412, array(self::class, 'validateCurrencyExists'), sprintf(
            'Currency %s does not found',
            $currency
        ));
        \api_validate(null, $currency, 412, array(self::class, 'validateNotCompanyCurrency'), sprintf(
            'Currency %s is the company currency and has no exchange rates',
            $currency
        ));

        $sql = get_sql_for_exchange_rates($currency);
        $result = db_query($sql, sprintf('Cannot query exchange rates in %s', $currency));
        $data = array();
        while ($result && $row = db_fetch_assoc($result)) {
            $rate = array(
                'id' => $row['id'],
                'currency' => $currency,
                'rateBuy' => $row['rate_buy'],
                'date' => sql2date($row['date_'])
            );
            $data[] = $rate;
        }
        \api_success_response($data);
    }

    /**
     * @SWG\Get(
     *   path="/exchangerates/{currency}/current",
     *   summary="Get current exchange rate for the given currency",
     *   tags={"exchangerates"},
     *   operationId="getCurrentExchangeRate",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     description="Currency of ExchangeRate to return",
     *     in="path",
     *     name="currency",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="successful operation",
     *     @SWG\Schema(
     *       type="object",
     *       ref="#/definitions/ExchangeRate"
     *     )
     *   ),
     *   deprecated=false
     * )
     */
    public function getCurrent($rest, $currency)
    {
        $currency = \strtoupper($currency);
        // Validation
        \api_validate(null, $currency, 412, array(self::class, 'validateCurrencyExists'), sprintf(
            'Currency %s not found',
            $currency
        ));
        \api_validate(null, $currency, 412, array(self::class, 'validateNotCompanyCurrency'), sprintf(
            'Currency %s is the company currency and has no exchange rates',
            $currency
        ));

        $date = today();
        $result = get_last_exchange_rate($currency, $date);
        if ($result == false) {
            \api_error('404', sprintf('No exchange rates found for %s', $currency));
        }
        \api_success_response(api_ensureAssociativeArray($result));
    }

    /**
     * @SWG\Get(
     *   path="/exchangerates/{currency}/{id}",
     *   summary="Get ExchangeRate by id",
     *   tags={"exchangerates"},
     *   operationId="getExchangeRate",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     description="Currency of ExchangeRate to return",
     *     in="path",
     *     name="currency",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     description="ID of ExchangeRate to return",
     *     in="path",
     *     name="exchangeRateId",
     *     required=true,
     *     type="integer",
     *     format="int64"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="successful operation",
     *     @SWG\Schema(
     *       type="object",
     *       ref="#/definitions/ExchangeRate"
     *     )
     *   ),
     *   deprecated=false
     * )
     */
    public function getById($rest, $currency, $id)
    {
        $currency = \strtoupper($currency);
        // Validation
        \api_validate(null, $currency, 412, array(self::class, 'validateCurrencyExists'), sprintf(
            'Currency %s not found',
            $currency
        ));
        \api_validate(null, $currency, 412, array(self::class, 'validateNotCompanyCurrency'), sprintf(
            'Currency %s is the company currency and has no exchange rates',
            $currency
        ));
        $result = get_exchange_rate($id);
        // error_log(var_export($result, true), 4);
        if ($result == false) {
            \api_error('404', sprintf('No exchange rate found for id %d', $id));
        }
        $data = array(
            'id' => $id,
            'currency' => $result['curr_code'],
            'rateBuy' => $result['rate_buy'],
            'rateSell' => $result['rate_sell'],
            'date' => $result['date_']
        );
        api_success_response(api_ensureAssociativeArray($data));
    }

    /**
     * @SWG\Post(
     *   path="/exchangerates/{currency}",
     *   summary="Add ExchangeRate",
     *   tags={"exchangerates"},
     *   operationId="addExchangeRate",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     description="Currency of ExchangeRate to return",
     *     in="path",
     *     name="currency",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="ExchangeRate to be added",
     *     required=true,
     *     @SWG\Schema(ref="#/definitions/ExchangeRate"),
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
    public function post($rest, $currency)
    {
        $currency = \strtoupper($currency);
        $req = $rest->request();
        $model = $req->post();
        // Validation
        \api_validate(null, $currency, 412, array(self::class, 'validateCurrencyExists'), sprintf(
            'Currency %s not found',
            $currency
        ));
        \api_validate(null, $currency, 412, array(self::class, 'validateNotCompanyCurrency'), sprintf(
            'Currency %s is the company currency and has no exchange rates',
            $currency
        ));
        \api_validate('rateBuy', $model);
        \api_check('rateSell', $model, $model['rateBuy']);
        \api_check('date', $model, Today());

        $curr_code = $currency;
        // error_log(var_export($model, true), 4);
        $date = $model['date'];
        // error_log(var_export($date, true), 4);
        $buy_rate = $model['rateBuy'];
        $sell_rate = $model['rateSell'];
        $sql = "INSERT INTO ".TB_PREF."exchange_rates (curr_code, date_, rate_buy, rate_sell)
            VALUES (".db_escape($curr_code).", '$date', ".db_escape($buy_rate)
            .", ".db_escape($sell_rate).")";
        db_query($sql, "could not add exchange rate for $curr_code");
        $id = db_insert_id();
        \api_create_response(array('id' => $id));
    }

    /**
     * @SWG\Put(
     *   path="/exchangerates/{currency}/{id}",
     *   summary="Update ExchangeRate",
     *   tags={"exchangerates"},
     *   operationId="updateExchangeRate",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     description="Currency of ExchangeRate to return",
     *     in="path",
     *     name="currency",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     description="ID of ExchangeRate to update",
     *     in="path",
     *     name="exchangeRateId",
     *     required=true,
     *     type="integer",
     *     format="int64"
     *   ),
     *   @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="ExchangeRate to be updated",
     *     required=true,
     *     @SWG\Schema(ref="#/definitions/ExchangeRate"),
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
    public function put($rest, $currency, $id)
    {
        $currency = \strtoupper($currency);
        $req = $rest->request();
        $model = $req->post();
        // Validation
        \api_validate(null, $currency, 412, array(self::class, 'validateCurrencyExists'), sprintf(
            'Currency %s not found',
            $currency
        ));
        \api_validate(null, $currency, 412, array(self::class, 'validateNotCompanyCurrency'), sprintf(
            'Currency %s is the company currency and has no exchange rates',
            $currency
        ));
        \api_validate('rateBuy', $model);
        \api_check('rateSell', $model, $model['rateBuy']);
        \api_check('date', $model, Today());

        $curr_code = $currency;
        $date = $model['date'];
        $buy_rate = $model['rateBuy'];
        $sell_rate = $model['rateSell'];
        $sql = "UPDATE ".TB_PREF."exchange_rates SET date_=".db_escape($date).", rate_buy=".db_escape($buy_rate).", rate_sell=".db_escape($sell_rate).
            " WHERE id=".db_escape($id);
        $result = db_query($sql, "could not update exchange rate for $curr_code $id");
        \api_success_response(array('id' => $id));
    }

    /**
     * @SWG\Delete(
     *   path="/exchangerates/{currency}/{id}",
     *   summary="Delete ExchangeRate",
     *   tags={"exchangerates"},
     *   operationId="deleteExchangeRate",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     description="Currency of ExchangeRate to delete",
     *     in="path",
     *     name="currency",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     description="ID of ExchangeRate to delete",
     *     in="path",
     *     name="exchangeRateId",
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
    public function delete($rest, $currency, $id)
    {
        $currency = \strtoupper($currency);
        // Validation
        \api_validate(null, $currency, 412, array(self::class, 'validateCurrencyExists'), sprintf(
            'Currency %s not found',
            $currency
        ));
        \api_validate(null, $currency, 412, array(self::class, 'validateNotCompanyCurrency'), sprintf(
            'Currency %s is the company currency and has no exchange rates',
            $currency
        ));
        delete_exchange_rate($id);
        \api_success_response(array('msg' => 'deleted', 'id' => $id));
    }
}
