<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model\Plugin;


use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection;

class Configurable
{
    /**
     * @var \Magento\Eav\Model\Config|\Magento\Swatches\Model\SwatchFactory
     */
    private $eavConfig;

    /**
     * @var \Magento\Swatches\Helper\Data
     */
    private $swatchHelper;

    /**
     * @var array
     */
    private $swatchAttributes;

    /**
     * @param \Magento\Swatches\Model\SwatchFactory $eavConfig
     * @param \Magento\Swatches\Helper\Data $swatchHelper
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Swatches\Helper\Data $swatchHelper
    ) {
        $this->eavConfig = $eavConfig;
        $this->swatchHelper = $swatchHelper;
    }

    /**
     * Returns Configurable Products Collection with added swatch attributes
     *
     * @param ConfigurableProduct $subject
     * @param Collection $result
     * @return Collection
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetUsedProductCollection(
        ConfigurableProductType $subject,
        Collection $result
    ) {
        if (!$this->swatchAttributes) {
            $this->swatchAttributes = ['image'];
            $entityType = $result->getEntity()->getType();
            foreach ($this->eavConfig->getEntityAttributeCodes($entityType) as $code) {
                $attribute = $this->eavConfig->getAttribute($entityType, $code);
                if (
                    $attribute->getData('additional_data')
                    && (
                        $this->swatchHelper->isVisualSwatch($attribute) || $this->swatchHelper->isTextSwatch($attribute)
                    )
                ) {
                    $this->swatchAttributes[] = $code;
                }
            }
        }
        $result->addAttributeToSelect($this->swatchAttributes);
        return $result;
    }
}
