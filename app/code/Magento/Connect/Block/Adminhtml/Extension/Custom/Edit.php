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
 * @package     Magento_Connect
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Extension edit page
 *
 * @category    Magento
 * @package     Magento_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Connect\Block\Adminhtml\Extension\Custom;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Constructor
     *
     * Initializes edit form container, adds necessary buttons
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId    = 'id';
        $this->_blockGroup  = 'Magento_Connect';
        $this->_controller  = 'adminhtml_extension_custom';

        parent::_construct();

        $this->_removeButton('back');
        $this->_updateButton('reset', 'onclick', "resetPackage()");

        $this->_addButton('create', array(
            'label'     => __('Save Data and Create Package'),
            'class'     => 'save',
            'onclick'   => "createPackage()",
            'data_attribute'  => array(
                'mage-init' => array(
                    'button' => array('event' => 'save', 'target' => '#edit_form'),
                ),
            ),
        ));
        $this->_addButton('save_as', array(
            'label'     => __('Save As...'),
            'title'     => __('Save package with custom package file name'),
            'onclick'   => 'saveAsPackage(event)',
            'data_attribute'  => array(
                'mage-init' => array(
                    'button' => array('event' => 'save', 'target' => '#edit_form'),
                ),
            ),
        ));
    }

    /**
     * Get header of page
     *
     * @return string
     */
    public function getHeaderText()
    {
        return __('New Extension');
    }

    /**
     * Get form submit URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('adminhtml/*/save');
    }
}
