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
 * Adminhtml dashboard diagram tabs
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
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('diagram_tab');
        $this->setDestElementId('diagram_tab_content');
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addTab(
            'orders',
            array(
                'label' => __('Orders'),
                'content' => $this->getLayout()->createBlock('Magento\Backend\Block\Dashboard\Tab\Orders')->toHtml(),
                'active' => true
            )
        );

        $this->addTab(
            'amounts',
            array(
                'label' => __('Amounts'),
                'content' => $this->getLayout()->createBlock('Magento\Backend\Block\Dashboard\Tab\Amounts')->toHtml()
            )
        );
        return parent::_prepareLayout();
    }
}
