<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Plugin\Catalog\Model\Product\Pricing\Renderer;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurable;

/**
 * A plugin for a salable resolver.
 */
class SalableResolver
{
    /**
     * @var TypeConfigurable
     */
    private $typeConfigurable;

    /**
     * @param TypeConfigurable $typeConfigurable
     */
    public function __construct(TypeConfigurable $typeConfigurable)
    {
        $this->typeConfigurable = $typeConfigurable;
    }

    /**
     * Performs an additional check whether given configurable product has
     * at least one configuration in-stock.
     *
     * @param \Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolver $subject
     * @param bool $result
     * @param \Magento\Framework\Pricing\SaleableInterface $salableItem
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsSalable(
        \Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolver $subject,
        $result,
        \Magento\Framework\Pricing\SaleableInterface $salableItem
    ) {
        if ($salableItem->getTypeId() === TypeConfigurable::TYPE_CODE && $result) {
            $result = $this->typeConfigurable->isSalable($salableItem);
        }

        return $result;
    }
}
