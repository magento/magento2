<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * DB adapter transaction interface that allows starting transaction with adjusted level,
 * transparently to the application
 */
namespace Magento\TestFramework\Db\Adapter;

interface TransactionInterface
{
    /**
     * Increment "transparent" transaction counter and start real transaction
     *
     * @return \Zend_Db_Adapter_Abstract
     */
    public function beginTransparentTransaction();

    /**
     * Decrement "transparent" transaction counter and commit real transaction
     *
     * @return \Zend_Db_Adapter_Abstract
     */
    public function commitTransparentTransaction();

    /**
     * Decrement "transparent" transaction counter and rollback real transaction
     *
     * @return \Zend_Db_Adapter_Abstract
     */
    public function rollbackTransparentTransaction();
}
