<?php

namespace Extrareality;

use Extrareality\Exceptions\ExtrarealityException;

class Client
{
    const API_URL = 'https://extrareality.by/api/';

    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';

    protected $datetime;
    protected $quest_id;
    protected $owner_id;
    protected $secret;

    public function __construct($owner_id, $secret, $quest_id = null)
    {
        $this->owner_id = $owner_id;
        $this->secret = $secret;
        $this->quest_id = $quest_id;
    }

    /**
     * @param string   $datetime
     * @param int|null $quest_id
     * @return mixed
     */
    public function book($datetime, $quest_id = null)
    {
        return $this->post('book', array(
            'datetime' => $datetime,
            'quest_id' => $quest_id,
        ));
    }

    public function pay($datetime, $quest_id = null)
    {
        // TBD
    }

    /**
     * Отменить бронь.
     *
     * @param $datetime
     * @param null $quest_id
     * @return bool
     */
    public function cancel($datetime, $quest_id = null)
    {
        try {
            $this->post('cancel', array(
                'datetime' => $datetime,
                'quest_id' => $quest_id,
            ));
        } catch (ExtrarealityException $e) {
            return false;
        }

        return true;
    }

    /**
     * @param string $datetime
     * @param null   $quest_id
     * @return mixed
     */
    public function check($datetime, $quest_id = null)
    {
        $result = $this->post('check', array(
            'datetime' => $datetime,
            'quest_id' => $quest_id,
        ));

        return json_decode($result, true);
    }

    /**
     * @param string $date
     * @param int    $quest_id
     * @return mixed
     */
    public function schedule($date, $quest_id)
    {
        $result = $this->get('schedule', array(
            'quest_id' => $quest_id,
            'datetime' => $date,
        ));

        return json_decode($result, true);
    }

    /**
     * @param string $date
     * @param int    $quest_id
     * @param array  $params   Может иметь параметры "newer_than_id", "quantity", "rating_threshold"
     * @return mixed
     */
    public function reviews($date, $quest_id, array $params = array())
    {
        $params = array_merge($params, [
            'quest_id' => $quest_id,
            'datetime' => $date,
        ]);
        $result = $this->get('reviews', $params);

        return json_decode($result, true);
    }

    /**
     * Генерирует подпись для запроса.
     *
     * @param string $datetime
     * @param int    $quest_id
     * @return string
     */
    protected function generateSignature($datetime, $quest_id)
    {
        return sha1($datetime . $quest_id . $this->owner_id . $this->secret);
    }

    /**
     * @param string $method
     * @param string $endPoint
     * @param array  $params
     * @return mixed
     * @throws ExtrarealityException
     */
    private function request($method, $endPoint, array $params = array())
    {
        if (empty($this->quest_id) && !isset($params['quest_id'])) {
            throw new ExtrarealityException('Параметр quest_id должен быть указан.');
        }

        if (!isset($params['datetime'])) {
            throw new ExtrarealityException('Параметр datetime должен быть указан.');
        }

        $quest_id = isset($params['quest_id']) ? $params['quest_id'] : $this->quest_id;

        $params = array_merge($params, array(
            'quest_id' => $quest_id,
            'owner_id' => $this->owner_id,
            'signature' => $this->generateSignature($params['datetime'], $quest_id),
        ));

        $curl = curl_init(static::API_URL . $endPoint);

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        if (static::METHOD_POST == $method) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        }

        $result = curl_exec($curl);
        $info = curl_getinfo($curl);
        $code = $info['http_code'];

        curl_close($curl);

        if ($code != 200) {
            throw new ExtrarealityException($result, $code);
        }

        return $result;
    }

    /**
     * @param string $endPoint
     * @param array  $params
     * @return mixed
     * @throws ExtrarealityException
     */
    private function get($endPoint, array $params = array())
    {
        return $this->request(static::METHOD_GET, $endPoint, $params);
    }

    /**
     * @param string $endPoint
     * @param array  $params
     * @return mixed
     * @throws ExtrarealityException
     */
    private function post($endPoint, array $params = array())
    {
        return $this->request(static::METHOD_POST, $endPoint, $params);
    }
}