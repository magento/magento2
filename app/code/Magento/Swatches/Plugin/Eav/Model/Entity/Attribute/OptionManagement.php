<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Swatches\Plugin\Eav\Model\Entity\Attribute;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product\Attribute\OptionManagement as CatalogOptionManagement;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
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
     * @param CatalogOptionManagement $subject
     * @param string $attributeCode
     * @param AttributeOptionInterface $option
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeAdd(
        CatalogOptionManagement $subject,
        ?string $attributeCode,
        AttributeOptionInterface $option
    ) {
        $attribute = $this->initAttribute($attributeCode);
        if (!$attribute) {
            return;
        }

        $optionId = $this->getNewOptionId($option);
        $this->setSwatchAttributeOption($attribute, $option, $optionId);
    }

    /**
     * Update swatch value of attribute option
     *
     * @param CatalogOptionManagement $subject
     * @param string $attributeCode
     * @param int $optionId
     * @param AttributeOptionInterface $option
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeUpdate(
        CatalogOptionManagement $subject,
        $attributeCode,
        $optionId,
        AttributeOptionInterface $option
    ) {
        $attribute = $this->initAttribute($attributeCode);
        if (!$attribute) {
            return;
        }

        $this->setSwatchAttributeOption($attribute, $option, (string)$optionId);
    }

    /**
     * Set attribute swatch option
     *
     * @param AttributeInterface $attribute
     * @param AttributeOptionInterface $option
     * @param string $optionId
     */
    private function setSwatchAttributeOption(
        AttributeInterface $attribute,
        AttributeOptionInterface $option,
        string $optionId
    ): void {
        $optionsValue = trim($option->getValue() ?: '');
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
     * Init swatch attribute
     *
     * @param string $attributeCode
     * @return bool|AttributeInterface
     */
    private function initAttribute($attributeCode)
    {
        if (empty($attributeCode)) {
            return false;
        }
        $attribute = $this->attributeRepository->get(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode
        );
        if (!$attribute || !$this->swatchHelper->isSwatchAttribute($attribute)) {
            return false;
        }

        return $attribute;
    }

    /**
     * Get option id to create new option
     *
     * @param AttributeOptionInterface $option
     * @return string
     */
    private function getNewOptionId(AttributeOptionInterface $option): string
    {
        $optionId = trim($option->getValue() ?: '');
        if (empty($optionId)) {
            $optionId = 'new_option';
        }

        return 'id_' . $optionId;
    }
}
