<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Parameter holder for transaction events
 */
namespace Magento\TestFramework\Event\Param;

class Transaction
{
    /**
     * @var bool
     */
    protected $_isStartRequested;

    /**
     * @var bool
     */
    protected $_isRollbackRequested;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_isStartRequested = false;
        $this->_isRollbackRequested = false;
    }

    /**
     * Request to start transaction
     */
    public function requestTransactionStart()
    {
        $this->_isStartRequested = true;
    }

    /**
     * Request to rollback transaction
     */
    public function requestTransactionRollback()
    {
        $this->_isRollbackRequested = true;
    }

    /**
     * Whether transaction start has been requested or not
     *
     * @return bool
     */
    public function isTransactionStartRequested()
    {
        return $this->_isStartRequested;
    }

    /**
     * Whether transaction rollback has been requested or not
     *
     * @return bool
     */
    public function isTransactionRollbackRequested()
    {
        return $this->_isRollbackRequested;
    }
}
