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

namespace Magento\Framework\Pricing\Price;

use Magento\Framework\Pricing\Object\SaleableInterface;

/**
 * Price factory
 */
class Factory
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    /**
     * Construct
     *
     * @param \Magento\Framework\ObjectManager $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create Price object for particular product
     *
     * @param SaleableInterface $saleableItem
     * @param string $className
     * @param float $quantity
     * @param array $arguments
     * @throws \InvalidArgumentException
     * @return \Magento\Framework\Pricing\Price\PriceInterface
     */
    public function create(SaleableInterface $saleableItem, $className, $quantity, array $arguments = [])
    {
        $arguments['saleableItem'] = $saleableItem;
        $arguments['quantity'] = $quantity;
        $price = $this->objectManager->create($className, $arguments);
        if (!$price instanceof PriceInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement \Magento\Framework\Pricing\Price\PriceInterface'
            );
        }
        return $price;
    }
}
