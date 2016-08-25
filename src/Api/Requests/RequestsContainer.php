<?php

namespace seregazhuk\SmsIntel\Api\Requests;

use ReflectionClass;
use seregazhuk\SmsIntel\Contracts\HttpClient;
use seregazhuk\SmsIntel\Exceptions\WrongRequest;

class RequestsContainer
{

    /**
     * @var HttpClient
     */
    protected $http;

    /**
     * @var string
     */
    protected $login;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var Request[]
     */
    protected $requests = [];

    public function __construct(HttpClient $http, $login, $password)
    {
        $this->http = $http;
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
     * Gets request object by name. If there is no such request
     * in requests array, it will try to create it, then save
     * it, and then return.
     *
     * @param string $requestClass
     *
     * @throws WrongRequest
     *
     * @return RequestInterface
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
     * @throws WrongRequest
     */
    public function resolveRequestByAction($action)
    {
        foreach ($this->getRequestsActionsMap() as $requestClass => $actions) {
            if(in_array($action, $actions)) {
                return $this->getRequest($requestClass);
            }
        }

        throw new WrongRequest("Action $action doesn't exist!");
    }

    /**
     * Creates request by class name, and if success saves
     * it to requests array.
     *
     * @param string $requestClass
     *
     * @throws WrongRequest
     */
    protected function addRequest($requestClass)
    {
        if (!class_exists($requestClass)) {
            throw new WrongRequest("Request $requestClass not found.");
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
            ->newInstanceArgs([$this->http])
            ->setCredentials($this->login, $this->password);
    }
}