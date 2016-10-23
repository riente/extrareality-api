<?php

namespace Extrareality\Response;

/**
 * Class ScheduleResponse
 * @package Extrareality\Response
 */
class ScheduleResponse extends AbstractApiResponse
{
    protected $bookings = [];

    /**
     * @param \DateTime $datetime
     * @param string    $name     Optional
     * @param string    $phone    Optional
     */
    public function addBookingToSchedule(\DateTime $datetime, $name = '-', $phone = '-')
    {
        $date = $datetime->format('Y-m-d');
        $time = $datetime->format('H:i:s');
        $key = $date.' '.$time;
        $this->bookings[$key] = [
            'time' => $time,
            'name' => $name,
            'phone' => $phone,
        ];
    }

    public function prepare()
    {
        $this->contentType = static::TYPE_JSON;
        $this->code = 200;
        $this->message = json_encode($this->bookings);
    }
}
