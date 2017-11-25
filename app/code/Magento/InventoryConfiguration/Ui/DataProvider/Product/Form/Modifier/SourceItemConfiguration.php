<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryConfigurationApi\Api\GetSourceItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;


/**
 * Product form modifier. Add to form source item configuration data
 */
class SourceItemConfiguration extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var GetSourceItemConfigurationInterface
     */
    private $getSourceItemConfiguration;

    /**
     * @param LocatorInterface $locator
     * @param GetSourceItemConfigurationInterface $getSourceItemConfiguration
     */
    public function __construct(
        LocatorInterface $locator,
        GetSourceItemConfigurationInterface $getSourceItemConfiguration
    ) {
        $this->locator = $locator;
        $this->getSourceItemConfiguration = $getSourceItemConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $product = $this->locator->getProduct();
        $assignedSources = $data[$product->getId()]['sources']['assigned_sources'];

        $data[$product->getId()]['sources']['assigned_sources'] = $this->getSourceItemsConfigurationData($assignedSources, $product);
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
            $sourceConfiguration = $this->getSourceItemConfiguration->get(
                (int)$source[SourceInterface::SOURCE_ID],
                $product->getSku()
            );

            $source[SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY] = $sourceConfiguration[SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY];
        }
        return $assignedSources;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
