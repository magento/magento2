<?php

namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Button;

use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\Registry;

class ShowParent extends Generic
{
    /**
     * @var Configurable
     */
    protected $configurableProduct;
    
    /**
     * ShowParent constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Configurable $configurableProduct
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Configurable $configurableProduct
    ) {
        parent::__construct($context, $registry);
        $this->configurableProduct = $configurableProduct;
    }
    
    /**
     * @return array
     */
    public function getButtonData()
    {
        $product = $this->getProduct();
        $configurableProduct = $this->configurableProduct->getParentIdsByChild($product->getId());
        
        if(!isset($configurableProduct[0]) || empty($configurableProduct[0])){
            return parent::getButtonData();
        }

        if ($product->getTypeId() == Type::TYPE_SIMPLE && $product->getVisibility() == Visibility::VISIBILITY_NOT_VISIBLE) {
            return [
                'label' => __('Show Parent'),
                'sort_order' => 10,
                'on_click' => 'setLocation(\'' . $this->getUrl('catalog/product/edit',
                        [
                            '_query' => ['id' => $configurableProduct[0]]
                        ]) . '\')'
            ];
        }
    }
}
