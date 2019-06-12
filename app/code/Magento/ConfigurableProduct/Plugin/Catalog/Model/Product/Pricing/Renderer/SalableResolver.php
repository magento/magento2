<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Plugin\Catalog\Model\Product\Pricing\Renderer;

<<<<<<< HEAD
use Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProviderInterface;
=======
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurable;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

/**
 * A plugin for a salable resolver.
 */
class SalableResolver
{
    /**
<<<<<<< HEAD
     * @var LowestPriceOptionsProviderInterface
     */
    private $lowestPriceOptionsProvider;

    /**
     * @param LowestPriceOptionsProviderInterface $lowestPriceOptionsProvider
     */
    public function __construct(
        LowestPriceOptionsProviderInterface $lowestPriceOptionsProvider
    ) {
        $this->lowestPriceOptionsProvider = $lowestPriceOptionsProvider;
    }

    /**
     * Performs an additional check whether given configurable product has
     * at least one configuration in-stock.
=======
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
     * Performs an additional check whether given configurable product has at least one configuration in-stock.
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     *
     * @param \Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolver $subject
     * @param bool $result
     * @param \Magento\Framework\Pricing\SaleableInterface $salableItem
<<<<<<< HEAD
     *
     * @return bool
     *
=======
     * @return bool
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsSalable(
        \Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolver $subject,
        $result,
        \Magento\Framework\Pricing\SaleableInterface $salableItem
    ) {
<<<<<<< HEAD
        if ($salableItem->getTypeId() == 'configurable' && $result) {
            $result = $salableItem->isSalable();
=======
        if ($salableItem->getTypeId() === TypeConfigurable::TYPE_CODE && $result) {
            $result = $this->typeConfigurable->isSalable($salableItem);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        }

        return $result;
    }
}
