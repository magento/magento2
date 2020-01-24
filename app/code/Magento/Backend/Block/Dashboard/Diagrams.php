<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Dashboard;

/**
 * Adminhtml dashboard diagram tabs
 * @deprecated dashboard graphs were migrated to dynamic chart.js solution
 * @see dashboard.diagrams in adminhtml_dashboard_index.xml
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Diagrams extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/tabshoriz.phtml';

    /**
     * Internal constructor, that is called from real constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('diagram_tab');
        $this->setDestElementId('diagram_tab_content');
    }

    /**
     * Preparing global layout
     *
     * @return $this
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
