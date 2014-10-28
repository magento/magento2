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
namespace Magento\UrlRewrite\Block\Catalog\Product;

/**
 * Products grid for URL rewrites editing
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Grid extends \Magento\Catalog\Block\Adminhtml\Product\Grid
{
    /**
     * Disable massaction
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        return $this;
    }

    /**
     * Prepare columns layout
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            array(
                'header' => __('ID'),
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            )
        );

        $this->addColumn('name', array('header' => __('Name'), 'index' => 'name'));

        $this->addColumn('sku', array('header' => __('SKU'), 'width' => 80, 'index' => 'sku'));
        $this->addColumn(
            'status',
            array(
                'header' => __('Status'),
                'width' => 50,
                'index' => 'status',
                'type' => 'options',
                'options' => $this->_status->getOptionArray()
            )
        );
        return $this;
    }

    /**
     * Get URL for dispatching grid ajax requests
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('adminhtml/*/productGrid', array('_current' => true));
    }

    /**
     * Return row url for js event handlers
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/*/edit', array('product' => $row->getId())) . 'category';
    }
}
