<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\ReportXml;

use Magento\Framework\DB\Select;

/**
 * Query object, contains SQL statement, information about connection, query arguments
 */
class Query implements \JsonSerializable
{
    /**
     * @var Select
     */
    private $select;

    /**
     * @var \Magento\Analytics\ReportXml\SelectHydrator
     */
    private $selectHydrator;

    /**
     * @var string
     */
    private $connectionName;

    /**
     * @var array
     */
    private $config;

    /**
     * Query constructor.
     *
     * @param Select $select
     * @param SelectHydrator $selectHydrator
     * @param string $connectionName
     * @param array $config
     */
    public function __construct(
        Select $select,
        SelectHydrator $selectHydrator,
        $connectionName,
        $config
    ) {
        $this->select = $select;
        $this->connectionName = $connectionName;
        $this->selectHydrator = $selectHydrator;
        $this->config = $config;
    }

    /**
     * @return Select
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * @return string
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'connectionName' => $this->getConnectionName(),
            'select_parts' => $this->selectHydrator->extract($this->getSelect()),
            'config' => $this->getConfig()
        ];
    }
}
