<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\ReportXml;

use Magento\Framework\DB\Select;

/**
 * Class Query
 *
 * Query object, contains SQL statement, information about connection, query arguments
 * @since 2.2.0
 */
class Query implements \JsonSerializable
{
    /**
     * @var Select
     * @since 2.2.0
     */
    private $select;

    /**
     * @var \Magento\Analytics\ReportXml\SelectHydrator
     * @since 2.2.0
     */
    private $selectHydrator;

    /**
     * @var string
     * @since 2.2.0
     */
    private $connectionName;

    /**
     * @var array
     * @since 2.2.0
     */
    private $config;

    /**
     * Query constructor.
     *
     * @param Select $select
     * @param SelectHydrator $selectHydrator
     * @param string $connectionName
     * @param array $config
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * @return string
     * @since 2.2.0
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * @return array
     * @since 2.2.0
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
     * @since 2.2.0
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
