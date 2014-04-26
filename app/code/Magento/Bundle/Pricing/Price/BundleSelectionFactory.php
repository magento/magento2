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

namespace Magento\Bundle\Pricing\Price;

use Magento\Framework\Pricing\Object\SaleableInterface;

/**
 * Bundle selection price factory
 */
class BundleSelectionFactory
{
    /**
     * Default selection class
     */
    const SELECTION_CLASS_DEFAULT = 'Magento\Bundle\Pricing\Price\BundleSelectionPriceInterface';

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
     * @param SaleableInterface $bundleProduct
     * @param SaleableInterface $selection
     * @param float $quantity
     * @param array $arguments
     * @throws \InvalidArgumentException
     * @return BundleSelectionPriceInterface
     */
    public function create(
        SaleableInterface $bundleProduct,
        SaleableInterface $selection,
        $quantity,
        array $arguments = []
    ) {
        $arguments['bundleProduct'] = $bundleProduct;
        $arguments['salableItem'] = $selection;
        $arguments['quantity'] = $quantity ? floatval($quantity) : 1.;
        $selectionPrice = $this->objectManager->create(self::SELECTION_CLASS_DEFAULT, $arguments);
        if (!$selectionPrice instanceof BundleSelectionPriceInterface) {
            throw new \InvalidArgumentException(
                get_class($selectionPrice) . ' doesn\'t implement BundleSelectionPriceInterface'
            );
        }
        return $selectionPrice;
    }
}
