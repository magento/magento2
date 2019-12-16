<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Cart\BuyRequest;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\QuoteGraphQl\Model\Cart\BuyRequest\BuyRequestDataProviderInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProductGraphQl\Model\Options\Collection as OptionCollection;
use Magento\Framework\EntityManager\MetadataPool;

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
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param ArrayManager $arrayManager
     * @param ProductRepositoryInterface $productRepository
     * @param OptionCollection $optionCollection
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        ArrayManager $arrayManager,
        ProductRepositoryInterface $productRepository,
        OptionCollection $optionCollection,
        MetadataPool $metadataPool
    ) {
        $this->arrayManager = $arrayManager;
        $this->productRepository = $productRepository;
        $this->optionCollection = $optionCollection;
        $this->metadataPool = $metadataPool;
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
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $this->optionCollection->addProductId((int)$parentProduct->getData($linkField));
        $options = $this->optionCollection->getAttributesByProductId((int)$parentProduct->getData($linkField));

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
