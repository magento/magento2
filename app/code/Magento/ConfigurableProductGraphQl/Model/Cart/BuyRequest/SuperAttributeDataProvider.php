<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Cart\BuyRequest;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\QuoteGraphQl\Model\Cart\BuyRequest\BuyRequestDataProviderInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProductGraphQl\Model\Options\Collection as OptionCollection;

/**
 * DataProvider for building super attribute options in buy requests
 */
class SuperAttributeDataProvider implements BuyRequestDataProviderInterface
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var OptionCollection
     */
    private $optionCollection;

    /**
     * SuperAttributeDataProvider constructor.
     * @param ArrayManager $arrayManager
     * @param ProductRepositoryInterface $productRepository
     * @param OptionCollection $optionCollection
     */
    public function __construct(
        ArrayManager $arrayManager,
        ProductRepositoryInterface $productRepository,
        OptionCollection $optionCollection
    ) {
        $this->arrayManager = $arrayManager;
        $this->productRepository = $productRepository;
        $this->optionCollection = $optionCollection;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $cartItemData): array
    {
        $parentSku = $this->arrayManager->get('parent_sku', $cartItemData);
        if ($parentSku === null) {
            return [];
        }
        $sku = $this->arrayManager->get('data/sku', $cartItemData);

        try {
            $parentProduct = $this->productRepository->get($parentSku);
            $product = $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__('Could not find specified product.'));
        }

        $this->optionCollection->addProductId((int)$parentProduct->getId());
        $options = $this->optionCollection->getAttributesByProductId((int)$parentProduct->getId());

        $superAttributesData = [];
        foreach ($options as $option) {
            $code = $option['attribute_code'];
            foreach ($option['values'] as $optionValue) {
                if ($optionValue['value_index'] === $product->getData($code)) {
                    $superAttributesData[$option['attribute_id']] = $optionValue['value_index'];
                    break;
                }
            }
        }
        return ['super_attribute' => $superAttributesData];
    }
}
