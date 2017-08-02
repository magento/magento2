<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Price Info factory
 */
namespace Magento\Framework\Pricing\PriceInfo;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\SaleableInterface;

/**
 * Price info model factory
 *
 * @api
 * @since 2.0.0
 */
class Factory
{
    /**
     * List of Price Info classes by product types
     *
     * @var array
     * @since 2.0.0
     */
    protected $types = [];

    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * Construct
     *
     * @param array $types
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(
        array $types,
        ObjectManagerInterface $objectManager
    ) {
        $this->types = $types;
        $this->objectManager = $objectManager;
    }

    /**
     * Create Price Info object for particular product
     *
     * @param SaleableInterface $saleableItem
     * @param array $arguments
     * @return \Magento\Framework\Pricing\PriceInfoInterface
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function create(SaleableInterface $saleableItem, array $arguments = [])
    {
        $type = $saleableItem->getTypeId();

        if (isset($this->types[$type]['infoClass'])) {
            $priceInfo = $this->types[$type]['infoClass'];
        } else {
            $priceInfo = $this->types['default']['infoClass'];
        }

        if (isset($this->types[$type]['prices'])) {
            $priceCollection = $this->types[$type]['prices'];
        } else {
            $priceCollection = $this->types['default']['prices'];
        }

        $arguments['saleableItem'] = $saleableItem;
        $quantity = $saleableItem->getQty();
        if ($quantity) {
            $arguments['quantity'] = $quantity;
        }

        $arguments['prices'] = $this->objectManager->create(
            $priceCollection,
            [
                'saleableItem' => $arguments['saleableItem'],
                'quantity' => $quantity
            ]
        );

        return $this->objectManager->create($priceInfo, $arguments);
    }
}
