<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Dashboard;

/**
 * Adminhtml dashboard diagram tabs
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Diagrams extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'Magento_Backend::widget/tabshoriz.phtml';

    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('diagram_tab');
        $this->setDestElementId('diagram_tab_content');
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->addTab(
            'orders',
            [
                'label' => __('Orders'),
                'content' => $this->getLayout()->createBlock(
                    \Magento\Backend\Block\Dashboard\Tab\Orders::class
                )->toHtml(),
                'active' => true
            ]
        );

        $this->addTab(
            'amounts',
            [
                'label' => __('Amounts'),
                'content' => $this->getLayout()->createBlock(
                    \Magento\Backend\Block\Dashboard\Tab\Amounts::class
                )->toHtml()
            ]
        );
        return parent::_prepareLayout();
    }
}
