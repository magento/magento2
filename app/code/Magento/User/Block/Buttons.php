<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Block;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Framework\Registry;

/**
 * Buttons block
 *
 * @api
 * @since 100.0.2
 */
class Buttons extends Template
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->getToolbar()->addChild(
            'backButton',
            Button::class,
            [
                'label' => __('Back'),
                'onclick' => 'window.location.href=\'' . $this->getUrl('*/*/') . '\'',
                'class' => 'back'
            ]
        );

        $this->getToolbar()->addChild(
            'resetButton',
            Button::class,
            ['label' => __('Reset'), 'onclick' => 'window.location.reload()', 'class' => 'reset']
        );

        if ((int)$this->getRequest()->getParam('rid')) {
            $this->getToolbar()->addChild(
                'deleteButton',
                Button::class,
                [
                    'label' => __('Delete Role'),
                    'onclick' => 'deleteConfirm(\'' . __(
                        'Are you sure you want to do this?'
                    ) . '\', \'' . $this->getUrl(
                        '*/*/delete',
                        ['rid' => $this->getRequest()->getParam('rid')]
                    ) . '\', {data: {}})',
                    'class' => 'delete'
                ]
            );
        }

        $this->getToolbar()->addChild(
            'saveButton',
            Button::class,
            [
                'label' => __('Save Role'),
                'class' => 'save primary save-role',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save', 'target' => '#role-edit-form']],
                ]
            ]
        );
        return parent::_prepareLayout();
    }

    /**
     * Get back button html
     *
     * @return string
     */
    public function getBackButtonHtml()
    {
        return $this->getChildHtml('backButton');
    }

    /**
     * Get reset button html
     *
     * @return string
     */
    public function getResetButtonHtml()
    {
        return $this->getChildHtml('resetButton');
    }

    /**
     * Get save button html
     *
     * @return string
     */
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('saveButton');
    }

    /**
     * Get delete button html
     *
     * @return string|void
     */
    public function getDeleteButtonHtml()
    {
        if ((int)$this->getRequest()->getParam('rid') == 0) {
            return;
        }
        return $this->getChildHtml('deleteButton');
    }

    /**
     * Get user
     *
     * @return mixed
     */
    public function getUser()
    {
        return $this->_coreRegistry->registry('user_data');
    }
}
