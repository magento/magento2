<?php
/**
 * Test framework custom connection adapter
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TestFramework\Db;

class ConnectionAdapter extends \Magento\Framework\Model\Resource\Type\Db\Pdo\Mysql
{
    /**
     * Retrieve DB adapter class name
     *
     * @return string
     */
    protected function _getDbAdapterClassName()
    {
        return 'Magento\TestFramework\Db\Adapter\Mysql';
    }
}
