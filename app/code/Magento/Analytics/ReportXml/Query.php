<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml;

/**
 * Class Query
 *
 * Query object, contains SQL statement, information about connection, query arguments
 */
class Query implements \JsonSerializable
{
    /**
     * @var string
     */
    private $queryString;

    /**
     * @var string 
     */
    private $connectionName;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * Query constructor.
     *
     * @param $queryString
     * @param $connectionName
     * @param array $parameters
     */
    public function __construct(
        $queryString,
        $connectionName,
        array $parameters
    ) {
        $this->queryString = $queryString;
        $this->connectionName = $connectionName;
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * @param string $queryString
     */
    public function setQueryString($queryString)
    {
        $this->queryString = $queryString;
    }

    /**
     * @return string
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * @param string $connectionName
     */
    public function setConnectionName($connectionName)
    {
        $this->connectionName = $connectionName;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return [
            'connectionName' => $this->getConnectionName(),
            'queryString' => $this->getQueryString(),
            'parameters' => $this->getParameters()
        ];
    }
}
