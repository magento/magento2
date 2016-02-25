<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Source;

use Magento\Framework\Data\ValueSourceInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;

/**
 * Class StockConfiguration
 */
class StockConfiguration implements ValueSourceInterface
{
    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(StockConfigurationInterface $stockConfiguration)
    {
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($name)
    {
        return $this->stockConfiguration->getDefaultConfigValue($name);
    }
}
