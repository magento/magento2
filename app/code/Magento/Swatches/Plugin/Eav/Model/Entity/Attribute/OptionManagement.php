<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Swatches\Plugin\Eav\Model\Entity\Attribute;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\AttributeRepository;
use Magento\Store\Model\Store;
use Magento\Swatches\Helper\Data;

/**
 * OptionManagement Plugin
 */
class OptionManagement
{
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;
    /**
     * @var Data
     */
    private $swatchHelper;

    /**
     * @param AttributeRepository $attributeRepository
     * @param Data $swatchHelper
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        Data $swatchHelper
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->swatchHelper = $swatchHelper;
    }

    /**
     * Add swatch value to the attribute option
     *
     * @param \Magento\Catalog\Model\Product\Attribute\OptionManagement $subject
     * @param string $attributeCode
     * @param \Magento\Eav\Api\Data\AttributeOptionInterface $option
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeAdd(
        \Magento\Catalog\Model\Product\Attribute\OptionManagement $subject,
        ?string $attributeCode,
        \Magento\Eav\Api\Data\AttributeOptionInterface $option
    ) {
        if (empty($attributeCode)) {
            return;
        }
        $attribute = $this->attributeRepository->get(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode
        );
        if (!$attribute || !$this->swatchHelper->isSwatchAttribute($attribute)) {
            return;
        }
        $optionId = $this->getOptionId($option);
        $optionsValue = $option->getValue();
        if ($this->swatchHelper->isVisualSwatch($attribute)) {
            $attribute->setData('swatchvisual', ['value' => [$optionId => $optionsValue]]);
        } else {
            $options = [];
            $options['value'][$optionId][Store::DEFAULT_STORE_ID] = $optionsValue;
            if (is_array($option->getStoreLabels())) {
                foreach ($option->getStoreLabels() as $label) {
                    if (!isset($options['value'][$optionId][$label->getStoreId()])) {
                        $options['value'][$optionId][$label->getStoreId()] = null;
                    }
                }
            }
            $attribute->setData('swatchtext', $options);
        }
    }

    /**
     * Returns option id
     *
     * @param \Magento\Eav\Api\Data\AttributeOptionInterface $option
     * @return string
     */
    private function getOptionId(\Magento\Eav\Api\Data\AttributeOptionInterface $option) : string
    {
        return 'id_' . ($option->getValue() ?: 'new_option');
    }
}
