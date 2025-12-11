<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\TestFramework\Db;

/**
 * Test framework custom connection adapter
 */
class ConnectionAdapter extends \Magento\Framework\Model\ResourceModel\Type\Db\Pdo\Mysql
{
    /**
     * Retrieve DB connection class name
     *
     * @return string
     */
    protected function getDbConnectionClassName()
    {
        return \Magento\TestFramework\Db\Adapter\Mysql::class;
    }
}
