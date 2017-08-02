<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget\Instance;

/**
 * Class \Magento\Widget\Controller\Adminhtml\Widget\Instance\Products
 *
 * @since 2.0.0
 */
class Products extends \Magento\Widget\Controller\Adminhtml\Widget\Instance
{
    /**
     * Products chooser Action (Ajax request)
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $selected = $this->getRequest()->getParam('selected', '');
        $productTypeId = $this->getRequest()->getParam('product_type_id', '');
        $chooser = $this->_view->getLayout()->createBlock(
            \Magento\Catalog\Block\Adminhtml\Product\Widget\Chooser::class
        )->setName(
            $this->mathRandom->getUniqueHash('products_grid_')
        )->setUseMassaction(
            true
        )->setProductTypeId(
            $productTypeId
        )->setSelectedProducts(
            explode(',', $selected)
        );
        /* @var $serializer \Magento\Backend\Block\Widget\Grid\Serializer */
        $serializer = $this->_view->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Grid\Serializer::class,
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
