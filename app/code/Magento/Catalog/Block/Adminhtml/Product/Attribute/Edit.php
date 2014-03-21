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
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Attribute;

/**
 * Product attribute edit page
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Block group name
     *
     * @var string
     */
    protected $_blockGroup = 'Magento_Catalog';

    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Registry $registry,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'attribute_id';
        $this->_controller = 'adminhtml_product_attribute';

        parent::_construct();

        if ($this->getRequest()->getParam('popup')) {
            $this->_removeButton('back');
            if ($this->getRequest()->getParam('product_tab') != 'variations') {
                $this->_addButton(
                    'save_in_new_set',
                    array(
                        'label' => __('Save in New Attribute Set'),
                        'class' => 'save',
                        'onclick' => 'saveAttributeInNewSet(\'' . __('Enter Name for New Attribute Set') . '\')'
                    )
                );
            }
        } else {
            $this->_addButton(
                'save_and_edit_button',
                array(
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => array(
                        'mage-init' => array(
                            'button' => array('event' => 'saveAndContinueEdit', 'target' => '#edit_form')
                        )
                    )
                ),
                100
            );
        }

        $this->_updateButton('save', 'label', __('Save Attribute'));
        $this->_updateButton('save', 'class', 'save primary');
        $this->_updateButton(
            'save',
            'data_attribute',
            array('mage-init' => array('button' => array('event' => 'save', 'target' => '#edit_form')))
        );

        $entityAttribute = $this->_coreRegistry->registry('entity_attribute');
        if (!$entityAttribute || !$entityAttribute->getIsUserDefined()) {
            $this->_removeButton('delete');
        } else {
            $this->_updateButton('delete', 'label', __('Delete Attribute'));
        }
    }

    /**
     * Retrieve header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('entity_attribute')->getId()) {
            $frontendLabel = $this->_coreRegistry->registry('entity_attribute')->getFrontendLabel();
            if (is_array($frontendLabel)) {
                $frontendLabel = $frontendLabel[0];
            }
            return __('Edit Product Attribute "%1"', $this->escapeHtml($frontendLabel));
        }
        return __('New Product Attribute');
    }

    /**
     * Retrieve URL for validation
     *
     * @return string
     */
    public function getValidationUrl()
    {
        return $this->getUrl('catalog/*/validate', array('_current' => true));
    }

    /**
     * Retrieve URL for save
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl(
            'catalog/product_attribute/save',
            array('_current' => true, 'back' => null, 'product_tab' => $this->getRequest()->getParam('product_tab'))
        );
    }
}
