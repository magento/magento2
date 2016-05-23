<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Dashboard;

/**
 * Adminhtml dashboard bottom tabs
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Grids extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/tabshoriz.phtml';

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('grid_tab');
        $this->setDestElementId('grid_tab_content');
    }

    /**
     * Prepare layout for dashboard bottom tabs
     *
     * To load block statically:
     *     1) content must be generated
     *     2) url should not be specified
     *     3) class should not be 'ajax'
     * To load with ajax:
     *     1) do not load content
     *     2) specify url (BE CAREFUL)
     *     3) specify class 'ajax'
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        // load this active tab statically
        $this->addTab(
            'ordered_products',
            [
                'label' => __('Bestsellers'),
                'content' => $this->getLayout()->createBlock(
                    'Magento\Backend\Block\Dashboard\Tab\Products\Ordered'
                )->toHtml(),
                'active' => true
            ]
        );

        // load other tabs with ajax
        $this->addTab(
            'reviewed_products',
            [
                'label' => __('Most Viewed Products'),
                'url' => $this->getUrl('adminhtml/*/productsViewed', ['_current' => true]),
                'class' => 'ajax'
            ]
        );

        $this->addTab(
            'new_customers',
            [
                'label' => __('New Customers'),
                'url' => $this->getUrl('adminhtml/*/customersNewest', ['_current' => true]),
                'class' => 'ajax'
            ]
        );

        $this->addTab(
            'customers',
            [
                'label' => __('Customers'),
                'url' => $this->getUrl('adminhtml/*/customersMost', ['_current' => true]),
                'class' => 'ajax'
            ]
        );

        return parent::_prepareLayout();
    }
}
