<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\System\Design\Edit;

/**
 * Class \Magento\Backend\Block\System\Design\Edit\Tabs
 *
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
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
