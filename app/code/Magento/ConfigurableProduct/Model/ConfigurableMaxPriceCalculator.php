<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model;

use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\App\ResourceConnection;

class ConfigurableMaxPriceCalculator
{
    /**
     * @var CalculatorInterface
     */
    private $calculator;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param CalculatorInterface $calculator
     * @param PriceCurrencyInterface $priceCurrency
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        CalculatorInterface $calculator,
        PriceCurrencyInterface $priceCurrency,
        ResourceConnection $resourceConnection
    ) {
        $this->calculator = $calculator;
        $this->priceCurrency = $priceCurrency;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get the maximum price of a configurable product.
     *
     * @param int $productId
     * @return float
     */
    public function getMaxPriceForConfigurableProduct($productId)
    {
        $connection = $this->resourceConnection->getConnection();
        $superLinkTable = $this->resourceConnection->getTableName('catalog_product_super_link');
        $catalogProductTable = $this->resourceConnection->getTableName('catalog_product_entity');
        $priceIndexTable = $this->resourceConnection->getTableName('catalog_product_index_price');
        $select = $connection->select()
            ->from(['sl' => $superLinkTable], [])
            ->join(['pe' => $catalogProductTable], 'sl.product_id = pe.entity_id', [])
            ->join(['ip' => $priceIndexTable], 'pe.entity_id = ip.entity_id', ['max_price' => 'MAX(ip.final_price)'])
            ->where('sl.parent_id = ?', $productId);
        $result = $connection->fetchRow($select);

        if ($result && isset($result['max_price'])) {
            return $result['max_price'];
        }

        // Return a default value or handle the case where there's no max price
        return 0.00;
    }
}
