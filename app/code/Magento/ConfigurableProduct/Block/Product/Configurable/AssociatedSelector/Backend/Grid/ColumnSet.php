<?php
/**
 * Block representing set of columns in product grid
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Product\Configurable\AssociatedSelector\Backend\Grid;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ColumnSet extends \Magento\Backend\Block\Widget\Grid\ColumnSet
{
    /**
     * Registry instance
     *
     * @var \Magento\Framework\Registry
     */
    protected $_registryManager;

    /**
     * Product type configurable instance
     *
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $_productType;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Backend\Model\Widget\Grid\Row\UrlGeneratorFactory $generatorFactory
     * @param \Magento\Backend\Model\Widget\Grid\SubTotals $subtotals
     * @param \Magento\Backend\Model\Widget\Grid\Totals $totals
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productType
     * @param \Magento\Framework\Registry $registryManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Backend\Model\Widget\Grid\Row\UrlGeneratorFactory $generatorFactory,
        \Magento\Backend\Model\Widget\Grid\SubTotals $subtotals,
        \Magento\Backend\Model\Widget\Grid\Totals $totals,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productType,
        \Magento\Framework\Registry $registryManager,
        array $data = []
    ) {
        parent::__construct($context, $generatorFactory, $subtotals, $totals, $data);

        $this->_registryManager = $registryManager;
        $this->_productType = $productType;
    }

    /**
     * Retrieve currently edited product object
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_registryManager->registry('current_product');
    }

    /**
     * Preparing layout
     *
     * @return \Magento\ConfigurableProduct\Block\Product\Configurable\AssociatedSelector\Backend\Grid\ColumnSet
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $product = $this->getProduct();
        $attributes = $this->_productType->getUsedProductAttributes($product);
        foreach ($attributes as $attribute) {
            /** @var $attribute \Magento\Catalog\Model\Entity\Attribute */
            /** @var $block \Magento\Backend\Block\Widget\Grid\Column */
            $block = $this->addChild(
                $attribute->getAttributeCode(),
                'Magento\Backend\Block\Widget\Grid\Column',
                [
                    'header' => $attribute->getStoreLabel(),
                    'index' => $attribute->getAttributeCode(),
                    'type' => 'options',
                    'options' => $this->getOptions($attribute->getSource()),
                    'sortable' => false
                ]
            );
            $block->setId($attribute->getAttributeCode())->setGrid($this);
        }
        return $this;
    }

    /**
     * Get option as hash
     *
     * @param \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource $sourceModel
     * @return array
     */
    private function getOptions(\Magento\Eav\Model\Entity\Attribute\Source\AbstractSource $sourceModel)
    {
        $result = [];
        foreach ($sourceModel->getAllOptions() as $option) {
            if ($option['value'] != '') {
                $result[] = $option;
            }
        }
        return $result;
    }
}
