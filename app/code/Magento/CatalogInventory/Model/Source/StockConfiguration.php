<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Source;

use Magento\Framework\Data\ValueSourceInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;

/**
 * Class StockConfiguration
 * @since 2.1.0
 */
class StockConfiguration implements ValueSourceInterface
{
    /**
     * @var StockConfigurationInterface
     * @since 2.1.0
     */
    protected $stockConfiguration;

    /**
     * @param StockConfigurationInterface $stockConfiguration
     * @since 2.1.0
     */
    public function __construct(StockConfigurationInterface $stockConfiguration)
    {
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getValue($name)
    {
        $value= $this->stockConfiguration->getDefaultConfigValue($name);
        return is_numeric($value) ? (float)$value : $value;
    }
}
