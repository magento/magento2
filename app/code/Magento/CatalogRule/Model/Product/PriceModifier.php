<?php
/**
 * Catalog rule product price modifier.
 *
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
namespace Magento\CatalogRule\Model\Product;

use Magento\Catalog\Model\Product\PriceModifierInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\RuleFactory;

class PriceModifier implements PriceModifierInterface
{
    /**
     * @var \Magento\CatalogRule\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * @param RuleFactory $ruleFactory
     */
    public function __construct(RuleFactory $ruleFactory)
    {
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * Modify price
     *
     * @param mixed $price
     * @param Product $product
     * @return mixed
     */
    public function modifyPrice($price, Product $product)
    {
        if ($price !== null) {
            $resultPrice = $this->ruleFactory->create()->calcProductPriceRule($product, $price);
            if ($resultPrice !== null) {
                $price = $resultPrice;
            }
        }
        return $price;
    }
}
