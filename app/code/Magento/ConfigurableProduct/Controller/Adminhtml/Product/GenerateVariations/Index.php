<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\GenerateVariations;

use Magento\Backend\App\Action;
use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Catalog\Model\Resource\Eav\AttributeFactory;

class Index extends Action
{
    /**
     * @var Product\Initialization\Helper
     */
    protected $initializationHelper;

    /**
     * @var Product\Builder
     */
    protected $productBuilder;

    /**
     * @var \Magento\Catalog\Model\Resource\Eav\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @param Action\Context $context
     * @param Product\Initialization\Helper $initializationHelper
     * @param Product\Builder $productBuilder
     * @param AttributeFactory $attributeFactory
     */
    public function __construct(
        Action\Context $context,
        Product\Initialization\Helper $initializationHelper,
        Product\Builder $productBuilder,
        AttributeFactory $attributeFactory
    ) {
        $this->initializationHelper = $initializationHelper;
        $this->productBuilder = $productBuilder;
        $this->attributeFactory = $attributeFactory;
        parent::__construct($context);
    }

    /**
     * Check for is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Catalog::products');
    }

    /**
     * Save attribute options just created by user
     *
     * @return void
     * @TODO Move this logic to configurable product type model
     *   when full set of operations for attribute options during
     *   product creation will be implemented: edit labels, remove, reorder.
     * Currently only addition of options to end and removal of just added option is supported.
     */
    protected function _saveAttributeOptions()
    {
        $productData = (array)$this->getRequest()->getParam('product');
        if (!isset($productData['configurable_attributes_data'])) {
            return;
        }

        foreach ($productData['configurable_attributes_data'] as &$attributeData) {
            $values = [];
            foreach ($attributeData['values'] as $valueId => $priceData) {
                if (isset($priceData['label'])) {
                    $attribute = $this->attributeFactory->create();
                    $attribute->load($attributeData['attribute_id']);
                    $optionsBefore = $attribute->getSource()->getAllOptions(false);

                    $attribute->setOption(
                        [
                            'value' => ['option_0' => [$priceData['label']]],
                            'order' => ['option_0' => count($optionsBefore) + 1],
                        ]
                    );
                    $attribute->save();

                    $attribute = $this->attributeFactory->create();
                    $attribute->load($attributeData['attribute_id']);
                    $optionsAfter = $attribute->getSource()->getAllOptions(false);

                    $newOption = array_pop($optionsAfter);

                    unset($priceData['label']);
                    $valueId = $newOption['value'];
                    $priceData['value_index'] = $valueId;
                }
                $values[$valueId] = $priceData;
            }
            $attributeData['values'] = $values;
        }

        $this->getRequest()->setParam('product', $productData);
    }

    /**
     * Generate product variations matrix
     *
     * @return void
     */
    public function execute()
    {
        $this->_saveAttributeOptions();
        $this->getRequest()->setParam('variations-matrix', []);
        $this->initializationHelper->initialize($this->productBuilder->build($this->getRequest()));
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
