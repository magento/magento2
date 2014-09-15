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
namespace Magento\Catalog\Service\V1\Data;

use Magento\Catalog\Service\V1\Data\Product as ProductDataObject;

/**
 * Product Model converter.
 *
 * Converts a Product Model to a Data Object or vice versa.
 */
class Converter
{
    /**
     * @var ProductBuilder
     */
    protected $productBuilder;

    /**
     * @param ProductBuilder $productBuilder
     */
    public function __construct(ProductBuilder $productBuilder)
    {
        $this->productBuilder = $productBuilder;
    }

    /**
     * Convert a product model to a product data entity
     *
     * @param \Magento\Catalog\Model\Product $productModel
     * @return \Magento\Catalog\Service\V1\Data\Product
     */
    public function createProductDataFromModel(\Magento\Catalog\Model\Product $productModel)
    {
        return $this->createProductBuilderFromModel($productModel)->create();
    }

    /**
     * Initialize product builder with product model data
     *
     * @param \Magento\Catalog\Model\Product $productModel
     * @return \Magento\Catalog\Service\V1\Data\ProductBuilder
     */
    public function createProductBuilderFromModel(\Magento\Catalog\Model\Product $productModel)
    {
        $this->populateBuilderWithAttributes($productModel);
        return $this->productBuilder;
    }

    /**
     * Loads the values from a product model
     *
     * @param \Magento\Catalog\Model\Product $productModel
     * @return void
     */
    protected function populateBuilderWithAttributes(\Magento\Catalog\Model\Product $productModel)
    {
        $attributes = array();
        foreach ($productModel->getAttributes() as $attribute) {
            $attrCode = $attribute->getAttributeCode();
            $value = $productModel->getDataUsingMethod($attrCode) ?: $productModel->getData($attrCode);
            if (null !== $value) {
                if ($attrCode != 'entity_id') {
                    $attributes[$attrCode] = $value;
                }
            }
        }
        $attributes[ProductDataObject::STORE_ID] = $productModel->getStoreId();
        $this->productBuilder->populateWithArray($attributes);
        return;
    }
}
