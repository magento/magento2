<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Controller\Adminhtml\Bundle\Selection;

/**
 * Class \Magento\Bundle\Controller\Adminhtml\Bundle\Selection\Grid
 *
 */
class Grid extends \Magento\Backend\App\Action
{
    /**
     * @return mixed
     */
    public function execute()
    {
        $index = $this->getRequest()->getParam('index');
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
