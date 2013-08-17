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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Poll answers resource model
 *
 * @category    Mage
 * @package     Mage_Poll
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Poll_Model_Resource_Poll_Answer extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize Poll_Answer resource
     *
     */
    protected function _construct()
    {
        $this->_init('poll_answer', 'answer_id');
    }

    /**
     * Initialize unique fields
     *
     * @return Mage_Poll_Model_Resource_Poll_Answer
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = array(array(
            'field' => array('answer_title', 'poll_id'),
            'title' => Mage::helper('Mage_Poll_Helper_Data')->__('You already used this answer title.')
        ));
        return $this;
    }
}
