<?php

namespace seregazhuk\SmsIntel\Api\Requests;

use GuzzleHttp\ClientInterface;
use ReflectionClass;
use seregazhuk\SmsIntel\Exceptions\WrongRequestException;

/**
 * @method send(string|array $phoneNumber, string $from, string $message) To send message to one phone number
 * @method getGroups
 * @method editGroup
 * @method addContact
 * @method getContacts
 * @method createGroup
 * @method getPhoneInfo
 * @method requestSource
 * @method removeContact
 *
 * @method cancel(int $smsId) Cancel sms by id
 * @method getBalance
 * @method checkCoupon
 * @method getReportBySms
 * @method getReportBySource
 * @method getReportByNumber
 */
class RequestsContainer
{
    /**
     * @var ClientInterface
     */
    protected $guzzle;

    /**
     * @var string
     */
    protected $login;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var AbstractRequest[]
     */
    protected $requests = [];

    public function __construct(ClientInterface $http, $login, $password)
    {
        $this->guzzle = $http;
        $this->login = $login;
        $this->password = $password;
    }

    /**
     * @return array
     */
    protected function getRequestsActionsMap()
    {
        return [
            XMLRequest::class  => XMLRequest::$allowedMethods,
            JSONRequest::class => JSONRequest::$allowedMethods,
        ];
    }

    /**
     * Proxies all methods to the appropriate Request object
     *
     * @param string $method
     * @param array $arguments
     * @return array
     */
    public function __call($method, $arguments)
    {
        $request = $this->resolveRequestByAction($method);

        return $request->$method(...$arguments);
    }

    /**
     * Gets request object by name. If there is no such request
     * in requests array, it will try to create it, then save
     * it, and then return.
     *
     * @param string $requestClass
     *
     * @throws WrongRequestException
     *
     * @return AbstractRequest
     */
    public function getRequest($requestClass)
    {
        // Check if an instance has already been initiated
        if (!isset($this->requests[$requestClass])) {
            $this->addRequest($requestClass);
        }
        return $this->requests[$requestClass];
    }

    /**
     * @param $action
     * @return string
     * @throws WrongRequestException
     */
    public function resolveRequestByAction($action)
    {
        foreach ($this->getRequestsActionsMap() as $requestClass => $actions) {
            if (in_array($action, $actions)) {
                return $this->getRequest($requestClass);
            }
        }

        throw new WrongRequestException("Action $action doesn't exist!");
    }

    /**
     * Creates request by class name, and if success saves
     * it to requests array.
     *
     * @param string $requestClass
     *
     * @throws WrongRequestException
     */
    protected function addRequest($requestClass)
    {
        if (!class_exists($requestClass)) {
            throw new WrongRequestException("Request $requestClass not found.");
        }
        $this->requests[$requestClass] = $this->buildRequest($requestClass);
    }

    /**
     * Build RequestInterface object with reflection API.
     *
     * @param string $className
     *
     * @return object
     */
    protected function buildRequest($className)
    {
        return (new ReflectionClass($className))
            ->newInstanceArgs([$this->guzzle])
            ->setCredentials($this->login, $this->password);
    }
}
