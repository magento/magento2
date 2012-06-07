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
 * @category    Magento
 * @package     Magento_Test
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * See Magento_Test_Db_TransactionInterface
 */
class Magento_Test_Db_Adapter_Mysql extends Varien_Db_Adapter_Pdo_Mysql
    implements Magento_Test_Db_Adapter_TransactionInterface
{
    /**
     * @var int
     */
    protected $_transparentLevel = 0;

    /**
     * See Magento_Test_Db_Adapter_TransactionInterface
     *
     * @return Magento_Test_Db_Adapter_Mysql
     */
    public function beginTransparentTransaction()
    {
        $this->_transparentLevel += 1;
        return $this->beginTransaction();
    }

    /**
     * See Magento_Test_Db_Adapter_TransactionInterface
     *
     * @return Magento_Test_Db_Adapter_Mysql
     */
    public function commitTransparentTransaction()
    {
        $this->_transparentLevel -= 1;
        return $this->commit();
    }

    /**
     * See Magento_Test_Db_Adapter_TransactionInterface
     *
     * @return Magento_Test_Db_Adapter_Mysql
     */
    public function rollbackTransparentTransaction()
    {
        $this->_transparentLevel -= 1;
        return $this->rollback();
    }

    /**
     * Adjust transaction level with "transparent" counter
     *
     * @return int
     */
    public function getTransactionLevel()
    {
        return parent::getTransactionLevel() - $this->_transparentLevel;
    }
}
