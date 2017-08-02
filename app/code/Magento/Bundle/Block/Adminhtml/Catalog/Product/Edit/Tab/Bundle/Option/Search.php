<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option;

/**
 * Bundle selection product block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Search extends \Magento\Backend\Block\Widget
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'product/edit/bundle/option/search.phtml';

    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->setId('bundle_option_selection_search');
    }

    /**
     * Create search grid
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'grid',
            $this->getLayout()->createBlock(
                \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Search\Grid::class,
                'adminhtml.catalog.product.edit.tab.bundle.option.search.grid'
            )
        );
        return parent::_prepareLayout();
    }

    /**
     * Prepare search grid
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _beforeToHtml()
    {
        $this->getChildBlock('grid')->setIndex($this->getIndex())->setFirstShow($this->getFirstShow());
        return parent::_beforeToHtml();
    }
}
