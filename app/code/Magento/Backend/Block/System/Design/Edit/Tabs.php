<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\System\Design\Edit;

/**
 * Class \Magento\Backend\Block\System\Design\Edit\Tabs
 *
 * @since 2.0.0
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('design_tabs');
        $this->setDestElementId('design-edit-form');
        $this->setTitle(__('Design Change'));
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->addTab(
            'general',
            [
                'label' => __('General'),
                'content' => $this->getLayout()->createBlock(
                    \Magento\Backend\Block\System\Design\Edit\Tab\General::class
                )->toHtml()
            ]
        );

        return parent::_prepareLayout();
    }
}
