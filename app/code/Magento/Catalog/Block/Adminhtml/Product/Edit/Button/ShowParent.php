<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Button;

use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;

/**
 * Class ShowParent
 */
class ShowParent extends Generic
{
    /**
     * @var Configurable
     */
    private $configurableProductType;

    /**
     * ShowParent constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Configurable $configurableProductType
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Configurable $configurableProductType
    ) {
        parent::__construct($context, $registry);
        $this->configurableProductType = $configurableProductType;
    }

    /**
     * Get Button Data
     *
     * @return array
     */
    public function getButtonData()
    {
        $product = $this->getProduct();
        $configurableProduct = $this->configurableProductType->getParentIdsByChild($product->getId());

        if (isset($configurableProduct[0]) && !empty($configurableProduct[0])) {
            return [
                'label' => __('Show Parent'),
                'sort_order' => 10,
                'on_click' => sprintf("location.href = '%s';", $this->getParentUrl($configurableProduct[0]))
            ];
        }
    }

    /**
     * Get URL for parent product
     *
     * @param $id
     * @return string
     */
    public function getParentUrl($id)
    {
        return $this->getUrl(
            'catalog/product/edit',
            ['id' => $id]
        );
    }
}
