<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        array $data = []
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
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $this->getUrl('adminhtml/*/') . '\')',
                'class' => 'back'
            ]
        );

        if ($this->getDesignChangeId()) {
            $this->getToolbar()->addChild(
                'delete_button',
                \Magento\Backend\Block\Widget\Button::class,
                [
                    'label' => __('Delete'),
                    'onclick' => 'deleteConfirm(\'' . __(
                        'Are you sure?'
                    ) . '\', \'' . $this->getDeleteUrl() . '\', {data: {}})',
                    'class' => 'delete'
                ]
            );
        }

        $this->getToolbar()->addChild(
            'save_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Save'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save', 'target' => '#design-edit-form']],
                ]
            ]
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
        return $this->getUrl('adminhtml/*/delete', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('adminhtml/*/save', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getValidationUrl()
    {
        return $this->getUrl('adminhtml/*/validate', ['_current' => true]);
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
