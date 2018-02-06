<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowStockNotification\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Inventory\Model\IsSourceItemsManagementAllowedForProductTypeInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryLowStockNotificationApi\Api\GetSourceItemConfigurationInterface;
use Magento\InventoryLowStockNotificationApi\Api\Data\SourceItemConfigurationInterface;

/**
 * Product form modifier. Add to form source item configuration data
 */
class SourceItemConfiguration extends AbstractModifier
{
    /**
     * @var IsSourceItemsManagementAllowedForProductTypeInterface
     */
    private $isSourceItemsManagementAllowedForProductType;

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var GetSourceItemConfigurationInterface
     */
    private $getSourceItemConfiguration;

    /**
     * @param IsSourceItemsManagementAllowedForProductTypeInterface $isSourceItemsManagementAllowedForProductType
     * @param LocatorInterface $locator
     * @param GetSourceItemConfigurationInterface $getSourceItemConfiguration
     */
    public function __construct(
        IsSourceItemsManagementAllowedForProductTypeInterface $isSourceItemsManagementAllowedForProductType,
        LocatorInterface $locator,
        GetSourceItemConfigurationInterface $getSourceItemConfiguration
    ) {
        $this->isSourceItemsManagementAllowedForProductType = $isSourceItemsManagementAllowedForProductType;
        $this->locator = $locator;
        $this->getSourceItemConfiguration = $getSourceItemConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $product = $this->locator->getProduct();
        if ($this->isSourceItemsManagementAllowedForProductType->execute($product->getTypeId()) === false) {
            return $data;
        }

        if (!isset($data[$product->getId()]['sources']['assigned_sources'])) {
            return $data;
        }

        $assignedSources = $data[$product->getId()]['sources']['assigned_sources'];
        $data[$product->getId()]['sources']['assigned_sources'] = $this->getSourceItemsConfigurationData(
            $assignedSources,
            $product
        );
        return $data;
    }

    /**
     * @param array $assignedSources
     * @param ProductInterface $product
     * @return array
     */
    private function getSourceItemsConfigurationData(array $assignedSources, ProductInterface $product): array
    {
        foreach ($assignedSources as &$source) {
            $sourceConfiguration = $this->getSourceItemConfiguration->execute(
                (string)$source[SourceInterface::SOURCE_CODE],
                $product->getSku()
            );

            $source[SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY] =
                $sourceConfiguration[SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY];
        }
        unset($source);
        return $assignedSources;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
