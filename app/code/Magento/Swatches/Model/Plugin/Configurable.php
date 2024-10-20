<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection;
use Magento\Eav\Model\Config;
use Magento\Swatches\Helper\Data as SwatchHelper;
use Magento\Swatches\Model\SwatchFactory;

class Configurable
{
    /**
     * @param Config|SwatchFactory $eavConfig
     * @param SwatchHelper $swatchHelper
     */
    public function __construct(
        private readonly Config $eavConfig,
        private readonly SwatchHelper $swatchHelper
    ) {
    }

    /**
     * Add swatch attributes to Configurable Products Collection
     *
     * @param ConfigurableProductType $subject
     * @param Collection $result
     * @param ProductInterface $product
     * @return Collection
     */
    public function afterGetUsedProductCollection(
        ConfigurableProductType $subject,
        Collection $result,
        ProductInterface $product
    ) {
        $swatchAttributes = ['image'];
        foreach ($subject->getUsedProductAttributes($product) as $code => $attribute) {
            if ($attribute->getData('additional_data')
                && (
                    $this->swatchHelper->isVisualSwatch($attribute) || $this->swatchHelper->isTextSwatch($attribute)
                )
            ) {
                $swatchAttributes[] = $code;
            }
        }
        $result->addAttributeToSelect($swatchAttributes);
        return $result;
    }
}
