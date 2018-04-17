<?php

namespace Extrareality;

use Extrareality\Exceptions\ExtrarealityException;

/**
 * Class ApiRequest
 * @package Extrareality
 */
class ApiRequest
{
    protected $date;
    protected $time;
    protected $isBooking;
    protected $isCancel;
    protected $isCheck;
    protected $isPay;
    protected $isSchedule;
    protected $questId;
    /** @var mixed $secret Salt needed to generate signature (provided by Extrareality) */
    protected $secret;
    protected $source;

    /**
     * ApiRequest constructor.
     *
     * @param mixed $secret
     * @throws ExtrarealityException
     */
    public function __construct($secret)
    {
        $this->secret = $secret;
        $this->processRequest();
    }

    /**
     * Возвращает строку в формате "Y-m-d H:i:s".
     *
     * @return string
     */
    public function getDateTime()
    {
        return $this->date.' '.$this->time;
    }

    /**
     * @return mixed
     */
    public function getQuestId()
    {
        return $this->questId;
    }

    /**
     * Gets the source of request.
     *
     * @return mixed
     * @throws ExtrarealityException
     */
    public function getSource()
    {
        if (is_null($this->source)) {
            // If the request is processed, then source should be at least "extrareality"
            $this->processRequest();
        }

        return $this->source;
    }

    /**
     * @return bool
     * @throws ExtrarealityException
     */
    public function isBooking()
    {
        if (is_null($this->isBooking)) {
            $this->processRequest();
        }

        return $this->isBooking;
    }

    /**
     * @return bool
     * @throws ExtrarealityException
     */
    public function isCancel()
    {
        if (is_null($this->isCancel)) {
            $this->processRequest();
        }

        return $this->isCancel;
    }

    /**
     * @return bool
     * @throws ExtrarealityException
     */
    public function isCheck()
    {
        if (is_null($this->isCheck)) {
            $this->processRequest();
        }

        return $this->isCheck;
    }

    /**
     * @return bool
     * @throws ExtrarealityException
     */
    public function isPay()
    {
        if (is_null($this->isPay)) {
            $this->processRequest();
        }

        return $this->isPay;
    }

    /**
     * @return bool
     * @throws ExtrarealityException
     */
    public function isSchedule()
    {
        if (is_null($this->isSchedule)) {
            $this->processRequest();
        }

        return $this->isSchedule;
    }

    /**
     * Обрабатывает переданные в запросе параметры.
     *
     * @throws ExtrarealityException
     */
    protected function processRequest()
    {
        $signature = $_REQUEST['signature'];
        $ownerId = (int) $_REQUEST['owner_id'];
        $this->questId = (int) $_REQUEST['quest_id'];
        $datetime = $_REQUEST['datetime'];

        if (!empty($_REQUEST['source'])) {
            $this->source = $_REQUEST['source'];
        } else {
            $this->source = 'extrareality';
        }

        // Проверяем подпись
        if ($signature != sha1($datetime . $this->questId . $ownerId . $this->secret)) {
            throw new ExtrarealityException('Подпись не верна.', 403);
        }

        if (!preg_match('/(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2})(:\d{2})?/', $datetime, $matches)) {
            throw new ExtrarealityException('Параметр datetime не указан или указан неверно.', 400);
        }

        $this->date = $matches[1];
        $this->time = $matches[2].':00';

        $uri = $_SERVER['REQUEST_URI'];
        $uri = explode('/', $uri);
        $method = end($uri);

        if (strpos($method, '?') !== false) {
            $method = explode('?', $method, 2);
            $method = $method[0];
        }

        $this->isBooking = false;
        $this->isCancel = false;
        $this->isCheck = false;
        $this->isPay = false;
        $this->isSchedule = false;

        switch ($method) {
            case 'book':
                $this->isBooking = true;
                break;

            case 'cancel':
                $this->isCancel = true;
                break;

            case 'check':
                $this->isCheck = true;
                break;

            case 'pay':
                $this->isPay = true;
                break;

            case 'schedule':
                $this->isSchedule = true;
                break;
        }
    }
}