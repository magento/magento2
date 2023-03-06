<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget\Instance;

use Magento\Backend\Block\Widget\Grid\Serializer;
use Magento\Catalog\Block\Adminhtml\Product\Widget\Chooser;
use Magento\Widget\Controller\Adminhtml\Widget\Instance;

class Products extends Instance
{
    /**
     * Products chooser Action (Ajax request)
     *
     * @return void
     */
    public function execute()
    {
        $selected = $this->getRequest()->getParam('selected', '');
        $productTypeId = $this->getRequest()->getParam('product_type_id', '');
        $chooser = $this->_view->getLayout()->createBlock(
            Chooser::class
        )->setName(
            $this->mathRandom->getUniqueHash('products_grid_')
        )->setUseMassaction(
            true
        )->setProductTypeId(
            $productTypeId
        )->setSelectedProducts(
            explode(',', $selected)
        );
        /* @var $serializer Serializer */
        $serializer = $this->_view->getLayout()->createBlock(
            Serializer::class,
            '',
            [
                'data' => [
                    'grid_block' => $chooser,
                    'callback' => 'getSelectedProducts',
                    'input_element_name' => 'selected_products',
                    'reload_param_name' => 'selected_products',
                ]
            ]
        );
        $this->setBody($chooser->toHtml() . $serializer->toHtml());
    }
}
