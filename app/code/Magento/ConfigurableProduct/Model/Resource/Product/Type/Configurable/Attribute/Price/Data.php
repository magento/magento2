<?php
/**
 * Catalog Configurable Product Attribute Collection
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

namespace Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute\Price;

/**
 * Class Data
 * Caching price for performance improvements of Configurable product loading
 * (Avoiding using static properties of Attribute Collection resource)
 * @todo Configurable Product models/resouces should be refactored with introduction of new entity(es),
 * such as ConfigurableOption (or OptionPrice, OptionPriceCollection)
 */
class Data
{
    /**
     * @var array
     */
    protected $prices;

    /**
     * @param int $productId
     * @param array $priceData
     * @return void
     */
    public function setProductPrice($productId, array $priceData)
    {
        $this->prices[$productId] = $priceData;
    }

    /**
     * @param int $productId
     * @return array|bool
     */
    public function getProductPrice($productId)
    {
        return isset($this->prices[$productId]) ? $this->prices[$productId] : false;
    }
}
