<?php
/**
 * Test framework custom connection adapter
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Db;

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
