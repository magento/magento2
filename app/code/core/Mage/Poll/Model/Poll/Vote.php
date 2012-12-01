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
 * @package     Mage_Poll
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Pool vote model
 *
 * @method Mage_Poll_Model_Resource_Poll_Vote _getResource()
 * @method Mage_Poll_Model_Resource_Poll_Vote getResource()
 * @method int getPollId()
 * @method Mage_Poll_Model_Poll_Vote setPollId(int $value)
 * @method int getPollAnswerId()
 * @method Mage_Poll_Model_Poll_Vote setPollAnswerId(int $value)
 * @method int getIpAddress()
 * @method Mage_Poll_Model_Poll_Vote setIpAddress(int $value)
 * @method int getCustomerId()
 * @method Mage_Poll_Model_Poll_Vote setCustomerId(int $value)
 * @method string getVoteTime()
 * @method Mage_Poll_Model_Poll_Vote setVoteTime(string $value)
 *
 * @category    Mage
 * @package     Mage_Poll
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Poll_Model_Poll_Vote extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('Mage_Poll_Model_Resource_Poll_Vote');
    }

    /**
     * Processing object before save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        if (!$this->getVoteTime()) {
            $this->setVoteTime(Mage::getSingleton('Mage_Core_Model_Date')->gmtDate());
        }
        return parent::_beforeSave();
    }
}
