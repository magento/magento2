<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Attribute;

use Magento\Backend\App\Action;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data;

/**
 * Creates options for product attributes
 */
class CreateOptions extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::products';

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var ProductAttributeInterface[]
     */
    private $attributes;

    /**
     * @param Action\Context $context
     * @param Data $jsonHelper
     * @param AttributeFactory $attributeFactory
     */
    public function __construct(
        Action\Context $context,
        Data $jsonHelper,
        AttributeFactory $attributeFactory
    ) {
        parent::__construct($context);
        $this->jsonHelper = $jsonHelper;
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * Search for attributes by part of attribute's label in admin store
     *
     * @return void
     */
    public function execute()
    {
        try {
            $output = $this->saveAttributeOptions();
        } catch (LocalizedException $e) {
            $output = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
        $this->getResponse()->representJson($this->jsonHelper->jsonEncode($output));
    }

    /**
     * Save attribute options just created by user
     *
     * @return array
     * @TODO Move this logic to configurable product type model
     *   when full set of operations for attribute options during
     *   product creation will be implemented: edit labels, remove, reorder.
     * Currently only addition of options is supported.
     * @throws LocalizedException
     */
    protected function saveAttributeOptions()
    {
        $attributeIds = $this->getUpdatedAttributeIds();
        $savedOptions = [];
        foreach ($attributeIds as $attributeId => $newOptions) {
            $attribute = $this->getAttribute($attributeId);
            $this->checkUnique($attribute, $newOptions);
            foreach ($newOptions as $newOption) {
                $lastAddedOption = $this->saveOption($attribute, $newOption);
                $savedOptions[$newOption['id']] = $lastAddedOption['value'];
            }
        }

        return $savedOptions;
    }

    /**
     * Checks unique values
     *
     * @param ProductAttributeInterface $attribute
     * @param array $newOptions
     * @return void
     * @throws LocalizedException
     */
    private function checkUnique(ProductAttributeInterface $attribute, array $newOptions)
    {
        $originalOptions = $attribute->getSource()->getAllOptions(false);
        $allOptions = array_merge($originalOptions, $newOptions);
        $optionValues = array_map(
            function ($option) {
                return $option['label'] ?? null;
            },
            $allOptions
        );

        $uniqueValues = array_unique(array_filter($optionValues));
        $duplicates = array_diff_assoc($optionValues, $uniqueValues);
        if ($duplicates) {
            throw new LocalizedException(__('The value of attribute ""%1"" must be unique', $attribute->getName()));
        }
    }

    /**
     * Loads the product attribute by the id
     *
     * @param int $attributeId
     * @return ProductAttributeInterface
     */
    private function getAttribute(int $attributeId)
    {
        if (!isset($this->attributes[$attributeId])) {
            $attribute = $this->attributeFactory->create();
            $this->attributes[$attributeId] = $attribute->load($attributeId);
        }

        return $this->attributes[$attributeId];
    }

    /**
     * Retrieve updated attribute ids with new options
     *
     * @return array
     */
    private function getUpdatedAttributeIds()
    {
        $options = (array)$this->getRequest()->getParam('options');
        $updatedAttributeIds = [];
        foreach ($options as $option) {
            if (isset($option['label'], $option['is_new'], $option['attribute_id'])) {
                $updatedAttributeIds[$option['attribute_id']][] = $option;
            }
        }

        return $updatedAttributeIds;
    }

    /**
     * Saves the option
     *
     * @param ProductAttributeInterface $attribute
     * @param array $newOption
     * @return array
     */
    private function saveOption(ProductAttributeInterface $attribute, array $newOption)
    {
        $optionsBefore = $attribute->getSource()->getAllOptions(false);
        $attribute->setOption(
            [
                'value' => ['option_0' => [$newOption['label']]],
                'order' => ['option_0' => count($optionsBefore) + 1],
            ]
        );
        $attribute->save();
        $optionsAfter = $attribute->getSource()->getAllOptions(false);
        return array_pop($optionsAfter);
    }
}
