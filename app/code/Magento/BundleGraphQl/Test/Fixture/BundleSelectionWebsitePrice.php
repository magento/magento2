<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Test\Fixture;

use Magento\Bundle\Model\ResourceModel\Selection as SelectionResource;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

/**
 * Overrides a fixed-price bundle product's option link price for a specific website.
 *
 * There is no core attribute-based fixture for per-website bundle selection price overrides,
 * so this mirrors what Magento\Bundle\Model\Selection::afterSave() does internally: load each
 * selection belonging to the bundle and save a website-scoped row via
 * Magento\Bundle\Model\ResourceModel\Selection::saveSelectionPrice(), leaving the default
 * (global) selection_price_value untouched.
 */
class BundleSelectionWebsitePrice implements RevertibleDataFixtureInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SelectionResource
     */
    private $selectionResource;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ResourceConnection $resourceConnection
     * @param SelectionResource $selectionResource
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ResourceConnection $resourceConnection,
        SelectionResource $selectionResource
    ) {
        $this->productRepository = $productRepository;
        $this->resourceConnection = $resourceConnection;
        $this->selectionResource = $selectionResource;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters
     * <pre>
     *  $data = [
     *    'sku'        => (string) Bundle product SKU. Required.
     *    'website_id' => (int) Website ID to scope the override to. Required.
     *    'price'      => (float) Website-scoped selection price value. Required.
     *    'price_type' => (int) Selection price type (0 - fixed, 1 - percent). Optional. Default: 0.
     *    'child_sku'  => (string) SKU of the linked (child) product identifying which single
     *                    selection to override. Optional. When omitted, every selection belonging
     *                    to the bundle is overridden to the same price (useful when a bundle has
     *                    only one selection, or all its selections intentionally share a price).
     *                    To give two selections different prices, call this fixture twice - once
     *                    per 'child_sku' - each targeting exactly one selection.
     *  ]
     * </pre>
     * @throws NoSuchEntityException if 'sku' does not exist, or 'child_sku' does not match any
     *  selection belonging to the bundle
     */
    public function apply(array $data = []): ?DataObject
    {
        $websiteId = (int)$data['website_id'];
        $bundleProduct = $this->productRepository->get($data['sku'], false, 0, true);
        $typeInstance = $bundleProduct->getTypeInstance();
        $optionsCollection = $typeInstance->getOptionsCollection($bundleProduct);
        $selectionsCollection = $typeInstance->getSelectionsCollection(
            $optionsCollection->getAllIds(),
            $bundleProduct
        );

        $childSku = $data['child_sku'] ?? null;
        $selectionIds = [];
        foreach ($selectionsCollection as $selection) {
            if ($childSku !== null && $selection->getSku() !== $childSku) {
                continue;
            }

            $selection->setWebsiteId($websiteId)
                ->setSelectionPriceType((int)($data['price_type'] ?? 0))
                ->setSelectionPriceValue((float)$data['price']);

            $this->selectionResource->saveSelectionPrice($selection);
            $selectionIds[] = (int)$selection->getSelectionId();
        }

        if ($childSku !== null && empty($selectionIds)) {
            throw new NoSuchEntityException(
                __('No bundle selection linking to child SKU "%1" was found on "%2".', $childSku, $data['sku'])
            );
        }

        return new DataObject(['selection_ids' => $selectionIds, 'website_id' => $websiteId]);
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $selectionIds = $data->getData('selection_ids') ?: [];
        if (empty($selectionIds)) {
            return;
        }

        $connection = $this->resourceConnection->getConnection();
        $connection->delete(
            $this->resourceConnection->getTableName('catalog_product_bundle_selection_price'),
            [
                'selection_id IN (?)' => $selectionIds,
                'website_id = ?' => (int)$data->getData('website_id'),
            ]
        );
    }
}
