<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\ValidationRules;

use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\ScopeInterface;

/**
 * @inheritdoc
 */
class ConfigurableProductsValidationRule implements QuoteValidationRuleInterface
{
    /**
     * @var string
     */
    private $generalMessage;

    /**
     * @var AllowedCountries
     */
    private $allowedCountryReader;

    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @param AllowedCountries $allowedCountryReader
     * @param ValidationResultFactory $validationResultFactory
     * @param string $generalMessage
     */
    public function __construct(
        AllowedCountries $allowedCountryReader,
        ValidationResultFactory $validationResultFactory,
        string $generalMessage = ''
    ) {
        $this->allowedCountryReader = $allowedCountryReader;
        $this->validationResultFactory = $validationResultFactory;
        $this->generalMessage = $generalMessage;
    }

    /**
     * @inheritdoc
     */
    public function validate(Quote $quote): array
    {
        $validationErrors = [];
        $cartAllowedChildren = [];
        $cartAllSimpleWithConfigurableParent = [];

        foreach ($quote->getItemsCollection() as $item) {
            $product = $item->getProduct();

            if (!$item->isDeleted()
                && $product->getTypeId() === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
            ) {
                $children = $product->getTypeInstance()->getUsedProducts($product);
                $cartAllowedChildren = array_merge($cartAllowedChildren, array_map(function ($item) {
                    return $item->getId();
                }, $children));
            } elseif (!$item->isDeleted() && $item->getParentItemId() && $item->getParentItem()) {
                $cartAllSimpleWithConfigurableParent[] = $product->getId();
            }
        }

        $notAvailableProducts = array_diff($cartAllSimpleWithConfigurableParent, $cartAllowedChildren);

        if (!empty($notAvailableProducts)) {
            $validationErrors = [__($this->generalMessage)];
        }

        return [$this->validationResultFactory->create(['errors' => $validationErrors])];
    }
}
