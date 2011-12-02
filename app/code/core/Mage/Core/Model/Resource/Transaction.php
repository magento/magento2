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
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Resource transaction model
 *
 * @todo need collect conection by name
 * @category   Mage
 * @package    Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Resource_Transaction
{
    /**
     * Objects which will be involved to transaction
     *
     * @var array
     */
    protected $_objects = array();

    /**
     * Transaction objects array with alias key
     *
     * @var array
     */
    protected $_objectsByAlias = array();

    /**
     * Callbacks array.
     *
     * @var array
     */
    protected $_beforeCommitCallbacks = array();
    /**
     * Begin transaction for all involved object resources
     *
     * @return Mage_Core_Model_Resource_Transaction
     */
    protected function _startTransaction()
    {
        foreach ($this->_objects as $object) {
            $object->getResource()->beginTransaction();
        }
        return $this;
    }

    /**
     * Commit transaction for all resources
     *
     * @return Mage_Core_Model_Resource_Transaction
     */
    protected function _commitTransaction()
    {
        foreach ($this->_objects as $object) {
            $object->getResource()->commit();
        }
        return $this;
    }

    /**
     * Rollback transaction
     *
     * @return Mage_Core_Model_Resource_Transaction
     */
    protected function _rollbackTransaction()
    {
        foreach ($this->_objects as $object) {
            $object->getResource()->rollBack();
        }
        return $this;
    }

    /**
     * Run all configured object callbacks
     *
     * @return Mage_Core_Model_Resource_Transaction
     */
    protected function _runCallbacks()
    {
        foreach ($this->_beforeCommitCallbacks as $callback) {
            call_user_func($callback);
        }
        return $this;
    }

    /**
     * Adding object for using in transaction
     *
     * @param Mage_Core_Model_Abstract $object
     * @param string $alias
     * @return Mage_Core_Model_Resource_Transaction
     */
    public function addObject(Mage_Core_Model_Abstract $object, $alias='')
    {
        $this->_objects[] = $object;
        if (!empty($alias)) {
            $this->_objectsByAlias[$alias] = $object;
        }
        return $this;
    }

    /**
     * Add callback function which will be called before commit transactions
     *
     * @param callback $callback
     * @return Mage_Core_Model_Resource_Transaction
     */
    public function addCommitCallback($callback)
    {
        $this->_beforeCommitCallbacks[] = $callback;
        return $this;
    }

    /**
     * Initialize objects save transaction
     *
     * @return Mage_Core_Model_Resource_Transaction
     * @throws Exception
     */
    public function save()
    {
        $this->_startTransaction();
        $error     = false;

        try {
            foreach ($this->_objects as $object) {
                $object->save();
            }
        } catch (Exception $e) {
            $error = $e;
        }

        if ($error === false) {
            try {
                $this->_runCallbacks();
            } catch (Exception $e) {
                $error = $e;
            }
        }

        if ($error) {
            $this->_rollbackTransaction();
            throw $error;
        } else {
            $this->_commitTransaction();
        }

        return $this;
    }

    /**
     * Initialize objects delete transaction
     *
     * @return Mage_Core_Model_Resource_Transaction
     * @throws Exception
     */
    public function delete()
    {
        $this->_startTransaction();
        $error = false;

        try {
            foreach ($this->_objects as $object) {
                $object->delete();
            }
        } catch (Exception $e) {
            $error = $e;
        }

        if ($error === false) {
            try {
                $this->_runCallbacks();
            } catch (Exception $e) {
                $error = $e;
            }
        }

        if ($error) {
            $this->_rollbackTransaction();
            throw $error;
        } else {
            $this->_commitTransaction();
        }
        return $this;
    }

}
