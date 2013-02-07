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
 * @package     Mage_User
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * User block
 *
 * @category   Mage
 * @package    Mage_User
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_User_Block_User extends Mage_Backend_Block_Widget_Grid_Container
{
    protected function _construct()
    {
        $this->addData(array(
            Mage_Backend_Block_Widget_Container::PARAM_CONTROLLER => 'user',
            Mage_Backend_Block_Widget_Grid_Container::PARAM_BLOCK_GROUP => 'Mage_User',
            Mage_Backend_Block_Widget_Grid_Container::PARAM_BUTTON_NEW => $this->__('Add New User'),
            Mage_Backend_Block_Widget_Container::PARAM_HEADER_TEXT => $this->__('Users'),
        ));
        parent::_construct();
    }

    /**
     * Prepare output HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        Mage::dispatchEvent('permissions_user_html_before', array('block' => $this));
        /** @var $model Mage_User_Model_Resource_User */
        $model = Mage::getObjectManager()->get('Mage_User_Model_Resource_User');
        if ($model->canCreateUser()) {
            $this->_addNewButton();
        }
        return parent::_toHtml();
    }
}
