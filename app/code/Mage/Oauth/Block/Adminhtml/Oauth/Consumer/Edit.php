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
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * OAuth Consumer Edit Block
 *
 * @category   Mage
 * @package    Mage_Oauth
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Oauth_Block_Adminhtml_Oauth_Consumer_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Consumer model
     *
     * @var Mage_Oauth_Model_Consumer
     */
    protected $_model;

    /**
     * Get consumer model
     *
     * @return Mage_Oauth_Model_Consumer
     */
    public function getModel()
    {
        if (null === $this->_model) {
            $this->_model = Mage::registry('current_consumer');
        }
        return $this->_model;
    }

    /**
     * Construct edit page
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_blockGroup = 'Mage_Oauth';
        $this->_controller = 'adminhtml_oauth_consumer';
        $this->_mode = 'edit';

        $this->_addButton('save_and_continue', array(
            'label'     => Mage::helper('Mage_Oauth_Helper_Data')->__('Save and Continue Edit'),
            'class' => 'save',
            'data_attribute'  => array(
                'mage-init' => array(
                    'button' => array('event' => 'saveAndContinueEdit', 'target' => '#edit_form'),
                ),
            ),
        ), 100);

        $this->_updateButton('save', 'label', $this->__('Save'));
        $this->_updateButton('save', 'id', 'save_button');
        $this->_updateButton('delete', 'label', $this->__('Delete'));

        /** @var $auth Mage_Core_Model_Authorization */
        $auth = Mage::getSingleton('Mage_Core_Model_Authorization');
        if (!$this->getModel() || !$this->getModel()->getId() || !$auth->isAllowed('Mage_Oauth::consumer_delete')) {
            $this->_removeButton('delete');
        }
    }

    /**
     * Get header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->getModel()->getId()) {
            return $this->__('Edit Consumer');
        } else {
            return $this->__('New Consumer');
        }
    }
}
