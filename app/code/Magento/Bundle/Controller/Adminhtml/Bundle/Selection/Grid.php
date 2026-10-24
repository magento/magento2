<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Controller\Adminhtml\Bundle\Selection;

use Magento\Catalog\Controller\Adminhtml\Product;

/**
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Grid extends Product
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $index = $this->getRequest()->getParam('index', '');
        if (!preg_match('/^[a-z0-9_.]*$/i', $index)) {
            throw new \InvalidArgumentException('Invalid parameter "index"');
        }

        return $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock(
                \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Search\Grid::class,
                'adminhtml.catalog.product.edit.tab.bundle.option.search.grid'
            )->setIndex($index)->toHtml()
        );
    }
}
