<?php

namespace Extrareality;

use Extrareality\Exceptions\ExtrarealityException;

class Client
{
    const API_URL = 'https://extrareality.by/api/';

    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';

    protected $datetime;
    protected $questId;
    protected $ownerId;
    protected $secret;

    /** @var mixed $url Custom API URL (if is sent to a site other than ER) */
    protected $url;
    /** @var mixed $source If sent from a site different from ER, we can indicate source */
    protected $source;

    public function __construct($ownerId, $secret, $questId = null)
    {
        $this->ownerId = $ownerId;
        $this->secret = $secret;
        $this->questId = $questId;
    }

    /**
     * @param string $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @param string $url
     */
    public function setApiUrl($url)
    {
        if (empty($url)) {
            return;
        }

        $this->url = rtrim($url, '/').'/';
    }

    /**
     * @param string   $datetime
     * @param int|null $questId
     * @return mixed
     * @throws ExtrarealityException
     */
    public function book($datetime, $questId = null)
    {
        return $this->post('book', [
            'datetime' => $datetime,
            'quest_id' => $questId,
        ]);
    }

    /**
     * @param array    $scheduleData
     * @param int|null $questId
     * @return mixed
     * @throws ExtrarealityException
     */
    public function updateSchedule(array $scheduleData, $questId = null)
    {
        return $this->postBody('update_schedule', json_encode($scheduleData), [
            'datetime' => date('Y-m-d H:i:s'),
            'quest_id' => $questId,
        ]);
    }

    public function pay($datetime, $questId = null)
    {
        // TBD
    }

    /**
     * Отменить бронь.
     *
     * @param $datetime
     * @param null $questId
     * @return bool
     */
    public function cancel($datetime, $questId = null)
    {
        try {
            $this->post('cancel', [
                'datetime' => $datetime,
                'quest_id' => $questId,
            ]);
        } catch (ExtrarealityException $e) {
            return false;
        }

        return true;
    }

    /**
     * @param string $datetime
     * @param null   $questId
     * @return mixed
     * @throws ExtrarealityException
     */
    public function check($datetime, $questId = null)
    {
        $result = $this->post('check', [
            'datetime' => $datetime,
            'quest_id' => $questId,
        ]);

        return json_decode($result, true);
    }

    /**
     * @param string $date
     * @param int    $questId
     * @return mixed
     * @throws ExtrarealityException
     */
    public function schedule($date, $questId)
    {
        $result = $this->get('schedule', [
            'quest_id' => $questId,
            'datetime' => $date,
        ]);

        return json_decode($result, true);
    }

    /**
     * @param string $date
     * @param int    $questId
     * @param array  $params   Может иметь параметры "newer_than_id", "quantity", "rating_threshold"
     * @return mixed
     * @throws ExtrarealityException
     */
    public function reviews($date, $questId, array $params = [])
    {
        $params = array_merge($params, [
            'quest_id' => $questId,
            'datetime' => $date,
        ]);
        $result = $this->get('reviews', $params);

        return json_decode($result, true);
    }

    /**
     * Генерирует подпись для запроса.
     *
     * @param string $datetime
     * @param int    $questId
     * @return string
     */
    protected function generateSignature($datetime, $questId)
    {
        return sha1($datetime.$questId.$this->ownerId.$this->secret);
    }

    /**
     * @param string $method
     * @param string $endPoint
     * @param array  $params
     * @return mixed
     * @throws ExtrarealityException
     */
    private function request($method, $endPoint, array $params = [])
    {
        $this->checkParams($params);

        $questId = isset($params['quest_id']) ? $params['quest_id'] : $this->questId;

        $params = array_merge($params, [
            'quest_id' => $questId,
            'owner_id' => $this->ownerId,
            'signature' => $this->generateSignature($params['datetime'], $questId),
            'source' => (!empty($this->source) ? $this->source : 'extrareality'),
        ]);

        $url = (!empty($this->url) ? $this->url : static::API_URL).$endPoint;
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        if (static::METHOD_POST == $method) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        } else {
            $query = http_build_query($params);
            curl_setopt($curl, CURLOPT_URL, $url.'?'.$query);
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
    private function get($endPoint, array $params = [])
    {
        return $this->request(static::METHOD_GET, $endPoint, $params);
    }

    /**
     * @param string $endPoint
     * @param array  $params
     * @return mixed
     * @throws ExtrarealityException
     */
    private function post($endPoint, array $params = [])
    {
        return $this->request(static::METHOD_POST, $endPoint, $params);
    }

    /**
     * @param string $endPoint
     * @param string $body
     * @param array $params
     * @return mixed
     * @throws ExtrarealityException
     */
    private function postBody($endPoint, $body, array $params = [])
    {
        $this->checkParams($params);

        $questId = isset($params['quest_id']) ? $params['quest_id'] : $this->questId;

        $params = array_merge($params, [
            'quest_id' => $questId,
            'owner_id' => $this->ownerId,
            'signature' => $this->generateSignature($params['datetime'], $questId),
            'source' => (!empty($this->source) ? $this->source : 'extrareality'),
        ]);

        $url = (!empty($this->url) ? $this->url : static::API_URL).$endPoint;
        $url .= '?'.http_build_query($params);
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: '.mb_strlen($body, 'utf-8')
        ]);

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
     * @param array $params
     * @throws ExtrarealityException
     */
    private function checkParams(array $params = [])
    {
        if (empty($this->questId) && !isset($params['quest_id'])) {
            throw new ExtrarealityException('Параметр quest_id должен быть указан.');
        }

        if (!isset($params['datetime'])) {
            throw new ExtrarealityException('Параметр datetime должен быть указан.');
        }
    }
}
