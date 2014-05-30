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

/**
 * Price Info factory
 */
namespace Magento\Framework\Pricing\PriceInfo;

use Magento\Framework\ObjectManager;
use Magento\Framework\Pricing\Object\SaleableInterface;

/**
 * Price info model factory
 */
class Factory
{
    /**
     * List of Price Info classes by product types
     *
     * @var array
     */
    protected $types = [];

    /**
     * Object Manager
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Construct
     *
     * @param array $types
     * @param \Magento\Framework\ObjectManager $objectManager
     */
    public function __construct(
        array $types,
        ObjectManager $objectManager
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
