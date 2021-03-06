<?php

namespace MBH\Bundle\ChannelManagerBundle\Lib\Ostrovok;

/**
 * Class OstrovokApiService
 * @package MBH\Bundle\ChannelManagerBundle\Lib\Ostrovok
 */
class OstrovokApiService
{

    const LIMIT = 0;
    /**
     *
     */
    //const API_URL = 'http://extrota-sandbox.ostrovok.ru/echannel/api/v0.1/';
    const API_URL = 'https://echannel.ostrovok.ru/echannel/api/v0.1/';

    /**
     * @var
     */
    protected $auth_token;

    /**
     * @var
     */
    protected $private_token;

    /**
     * OstrovokApiService constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->auth_token = $config['ostrovok']['username'];
        $this->private_token = $config['ostrovok']['password'];
    }

    /**
     * @param $data
     * @return array|string
     */
    private function createSignatureString($data)
    {
        $isList = false;
        if (is_array($data) && count($data)) {
            if ($data[0]??false) {
                $isList = true;
            }
        }

        if (is_array($data) && !$isList) {
            ksort($data);
            $tmp = array();
            foreach ($data as $key => $value) {
                $tmp[] = array($this->createSignatureString($key), $this->createSignatureString($data[$key]));
            }
            $result = array();
            foreach ($tmp as $key => $value) {
                $result[] = implode("=", $value);
            }

            return implode(";", $result);
        } elseif (is_array($data) && $isList) {
            $result = array();
            foreach ($data as $value) {
                $result[] = $this->createSignatureString($value);
            }
            $result = implode(";", $result);
            if (count($data) > 1) {
                $result = ("[" . $result . "]");
            }

            return $result;
        } elseif (is_bool($data)) {
            return $data ? "true" : "false";
        } elseif (is_null($data)) {
            return 'None';
        }

        return (string)$data;
    }

    /**
     * @param array $data
     * @param $private
     * @return string
     */
    private function getSignature(array $data, $private)
    {
        $data['private'] = $private;
        $signatureString = $this->createSignatureString($data);
        return md5($signatureString);
    }

    /**
     * @param $api_method
     * @param array $data
     * @return mixed|string
     * @throws OstrovokApiServiceException
     */
    private function callGet($api_method, array $data)
    {
        $data["token"] = $this->auth_token;
        $data["limits"] = self::LIMIT;
        $data["sign"] = $this->getSignature($data, $this->private_token);
        $request = self::API_URL . $api_method . "?" . http_build_query($data) . "&";

        $response = file_get_contents($request);
        if (!$response) {
            throw new OstrovokApiServiceException('No returned request in callGet Method '.get_class($this));
        }
        $response = json_decode($response, true);
        $this->checkErrors($response);
        return $response;
    }

    /**
     * @param $api_method
     * @param array $data
     * @param array $get_data
     * @return mixed
     */
    private function callPUT($api_method, array $data, array $get_data = array())
    {
        return $this->makeCall("PUT", $api_method, $data, $get_data);
    }

    /**
     * @param $api_method
     * @param array $data
     * @param array $get_data
     * @return mixed
     */
    private function callPOST($api_method, array $data, array $get_data = array())
    {
        return $this->makeCall("POST", $api_method, $data, $get_data);
    }

    /**
     * @param string $type
     * @param string $api_method
     * @param array $data
     * @param array $get_data
     * @return mixed
     */
    private function makeCall(string $type, string $api_method, array $data, array $get_data = array())
    {
        $signature_data = $data;
        $signature_data["token"] = $this->auth_token;
        $get_data["token"] = $this->auth_token;
        $get_data["sign"] = $this->getSignature($signature_data, $this->private_token);

        $final_url = self::API_URL . $api_method . "?" . http_build_query($get_data);
        $curl = curl_init($final_url);

        $data_json = json_encode($data);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_json),
        ));

        $response = json_decode(curl_exec($curl), true);
        curl_close($curl);
        $this->checkErrors($response);

        return $response;
    }

    /**
     * @param $response
     * @throws OstrovokApiServiceException
     */
    private function checkErrors($response)
    {
        if (!empty($response['error'])) {
            throw new OstrovokApiServiceException(
                is_array($response['error']) ? http_build_query($response['error']) : $response['error']
            );
        };
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    public function getHotels(array $data = array())
    {
        return $this->callGet("hotels/", $data);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function getRoomCategories(array $data = array())
    {
        $response = $this->callGet("room_categories/", $data);

        return $response['room_categories'];
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    public function getMealPlans(array $data = array())
    {
        $answer = $this->callGet("meal_plans/", $data);

        return $answer['meal_plans'];
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    public function getOrders(array $data = array())
    {
        return $this->callGet("orders/", $data);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function getBookings(array $data = array())
    {
        $response = $this->callGet("bookings/", $data);
        return $response['bookings'];
    }

    /**
     * @param $plan_date_start_at
     * @param $plan_date_end_at
     * @param array $data
     * @return mixed|string
     */
    public function getRNA($plan_date_start_at, $plan_date_end_at, array $data = array())
    {
        $data["plan_date_start_at"] = $plan_date_start_at;
        $data["plan_date_end_at"] = $plan_date_end_at;
        return $this->callGet("rna/", $data);
    }

    /**
     * @param array $data
     * @param bool $byKey
     * @return array
     */
    public function getOccupancies(array $data = array(), bool $byKey = false)
    {
        $result = [];
        $response = $this->callGet("occupancies/", $data);
        $data = $response['occupancies'];
        if ($byKey) {
            foreach ($data as $occupancy) {
                $result[$occupancy['id']] = $occupancy;
            }
        } else {
            $result = $data;
        }

        return $result;
    }

    /**
     * @param array $data
     * @param bool $isShowDeleted
     * @return array
     */
    public function getRatePlans(array $data = array(), $isShowDeleted = false)
    {
        $response = $this->callGet("rate_plans/", $data);

        $rate_plans = [];
        foreach ($response['rate_plans'] as $rate) {
            if($rate['status'] === 'X' && !$isShowDeleted) continue;
            $rate_plans[] = $rate;
        }

        return $rate_plans;

    }

    /**
     * @param array $data
     * @return mixed
     */
    public function updateRNA(array $data = array())
    {
        return $this->callPUT("rna/", $data);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function createRNA(array $data = array())
    {
        return $this->callPOST("rna/", $data);
    }

    /**
     * @param null $hotel
     * @param null $room_category
     * @param array $rate_plan_params
     * @return mixed
     */
    public function createRatePlan($hotel = null, $room_category = null, array $rate_plan_params)
    {
        $get_data = array();
        if (!is_null($hotel)) {
            $get_data["hotel"] = $hotel;
        }
        if (!is_null($room_category)) {
            $get_data["room_category"] = $room_category;
        }
        return $this->callPOST("rate_plans/", $rate_plan_params, $get_data);
    }

    /**
     * @param $id
     * @param null $hotel
     * @param null $room_category
     * @param array $rate_plan_params
     * @return mixed
     */
    public function updateRatePlan($id, $hotel = null, $room_category = null, array $rate_plan_params = array())
    {
        $get_data = array();
        $get_data["id"] = $id;
        if (!is_null($hotel)) {
            $get_data["hotel"] = $hotel;
        }
        if (!is_null($room_category)) {
            $get_data["room_category"] = $room_category;
        }
        return $this->callPUT("rate_plans/", $rate_plan_params, $get_data);
    }
}