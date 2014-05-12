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
            array(
                'label' => __('Bestsellers'),
                'content' => $this->getLayout()->createBlock(
                    'Magento\Backend\Block\Dashboard\Tab\Products\Ordered'
                )->toHtml(),
                'active' => true
            )
        );

        // load other tabs with ajax
        $this->addTab(
            'reviewed_products',
            array(
                'label' => __('Most Viewed Products'),
                'url' => $this->getUrl('adminhtml/*/productsViewed', array('_current' => true)),
                'class' => 'ajax'
            )
        );

        $this->addTab(
            'new_customers',
            array(
                'label' => __('New Customers'),
                'url' => $this->getUrl('adminhtml/*/customersNewest', array('_current' => true)),
                'class' => 'ajax'
            )
        );

        $this->addTab(
            'customers',
            array(
                'label' => __('Customers'),
                'url' => $this->getUrl('adminhtml/*/customersMost', array('_current' => true)),
                'class' => 'ajax'
            )
        );

        return parent::_prepareLayout();
    }
}
