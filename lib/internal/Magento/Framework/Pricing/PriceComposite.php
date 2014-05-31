<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Pricing;

use Magento\Framework\Pricing\Object\SaleableInterface;
use Magento\Framework\Pricing\Price\Factory as PriceFactory;
use Magento\Framework\Pricing\Price\PriceInterface;

/**
 * Composite price model
 */
class PriceComposite
{
    /**
     * @var PriceFactory
     */
    protected $priceFactory;

    /**
     * @var array
     */
    protected $metadata;

    /**
     * @param PriceFactory $priceFactory
     * @param array $metadata
     */
    public function __construct(PriceFactory $priceFactory, array $metadata = [])
    {
        $this->priceFactory = $priceFactory;
        $this->metadata = $metadata;
    }

    /**
     * @return array
     */
    public function getPriceCodes()
    {
        return array_keys($this->metadata);
    }

    /**
     * Returns metadata for prices
     *
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param SaleableInterface $salableItem
     * @param string $priceCode
     * @param float $quantity
     * @return PriceInterface
     * @throws \InvalidArgumentException
     */
    public function createPriceObject(SaleableInterface $salableItem, $priceCode, $quantity)
    {
        if (!isset($this->metadata[$priceCode])) {
            throw new \InvalidArgumentException($priceCode . ' is not registered in prices list');
        }
        $className = $this->metadata[$priceCode]['class'];
        return $this->priceFactory->create($salableItem, $className, $quantity);
    }
}
