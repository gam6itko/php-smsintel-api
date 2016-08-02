<?php

namespace seregazhuk\SmsIntel\Factories;

use seregazhuk\SmsIntel\Sender;
use seregazhuk\SmsIntel\Request;
use seregazhuk\SmsIntel\Exceptions\AuthException;
use seregazhuk\SmsIntel\Adapters\GuzzleHttpAdapter;

class SmsIntel
{
    /**
     * @param $login
     * @param $password
     * @return Sender
     */
    public static function create($login, $password)
    {
        self::checkCredentials($login, $password);

        return new Sender(new Request(
            new GuzzleHttpAdapter(), $login, $password
        ));
    }

    /**
     * @param string $login
     * @param string $password
     * @throws AuthException
     */
    protected static function checkCredentials($login, $password)
    {
        if (empty($login) || empty($password)) {
            throw new AuthException('You must provide login and password to send messages!');
        }
    }
}