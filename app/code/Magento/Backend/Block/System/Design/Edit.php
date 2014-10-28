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
namespace Magento\Backend\Block\System\Design;

class Edit extends \Magento\Backend\Block\Widget
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::system/design/edit.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
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
        parent::_construct();

        $this->setId('design_edit');
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $this->getToolbar()->addChild(
            'back_button',
            'Magento\Backend\Block\Widget\Button',
            array(
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $this->getUrl('adminhtml/*/') . '\')',
                'class' => 'back'
            )
        );

        if ($this->getDesignChangeId()) {
            $this->getToolbar()->addChild(
                'delete_button',
                'Magento\Backend\Block\Widget\Button',
                array(
                    'label' => __('Delete'),
                    'onclick' => 'confirmSetLocation(\'' . __(
                        'Are you sure?'
                    ) . '\', \'' . $this->getDeleteUrl() . '\')',
                    'class' => 'delete'
                )
            );
        }

        $this->getToolbar()->addChild(
            'save_button',
            'Magento\Backend\Block\Widget\Button',
            array(
                'label' => __('Save'),
                'class' => 'save primary',
                'data_attribute' => array(
                    'mage-init' => array('button' => array('event' => 'save', 'target' => '#design-edit-form'))
                )
            )
        );

        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getDesignChangeId()
    {
        return $this->_coreRegistry->registry('design')->getId();
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('adminhtml/*/delete', array('_current' => true));
    }

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('adminhtml/*/save', array('_current' => true));
    }

    /**
     * @return string
     */
    public function getValidationUrl()
    {
        return $this->getUrl('adminhtml/*/validate', array('_current' => true));
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        if ($this->_coreRegistry->registry('design')->getId()) {
            $header = __('Edit Design Change');
        } else {
            $header = __('New Store Design Change');
        }
        return $header;
    }
}
