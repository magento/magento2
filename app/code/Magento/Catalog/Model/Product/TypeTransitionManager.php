<?php
/**
 * Product type transition manager
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
namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Model\Product;

class TypeTransitionManager
{
    /**
     * List of compatible product types
     *
     * @var array
     */
    protected $compatibleTypes;

    /**
     * @param array $compatibleTypes
     */
    public function __construct(array $compatibleTypes)
    {
        $this->compatibleTypes = $compatibleTypes;
    }

    /**
     * Process given product and change its type if needed
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function processProduct(Product $product)
    {
        if (in_array($product->getTypeId(), $this->compatibleTypes)) {
            $product->setTypeInstance(null);
            $productTypeId = $product->hasIsVirtual() ? \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL : \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE;
            $product->setTypeId($productTypeId);
        }
    }
}
