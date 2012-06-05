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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Mage_User role block
 *
 * @category   Mage
 * @package    Mage_User
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_User_Block_Role extends Mage_Backend_Block_Widget_Grid_Container
{
    /**
     * @var string
     */
    protected $_controller = 'user_role';

    /**
     * @var string
     */
    protected $_blockGroup = 'Mage_User';


    public function __construct()
    {
        $this->_headerText = Mage::helper('Mage_User_Helper_Data')->__('Roles');
        $this->_addButtonLabel = Mage::helper('Mage_User_Helper_Data')->__('Add New Role');
        parent::__construct();
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/editrole');
    }

    protected function _prepareLayout()
    {
        if (!$this->getLayout()->getChildName($this->getNameInLayout(), 'grid')) {
            $this->setChild(
                'grid',
                $this->getLayout()->createBlock(
                    $this->_blockGroup . '_Block_Role_Grid',
                    $this->_controller . '.grid')
                    ->setSaveParametersInSession(true)
            );
        }
        return Mage_Backend_Block_Widget_Container::_prepareLayout();
    }

    /**
     * Prepare output HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        Mage::dispatchEvent('permissions_role_html_before', array('block' => $this));
        return parent::_toHtml();
    }
}
