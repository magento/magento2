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

use \Magento\Framework\Service\ExtensibleDataObjectConverter;

class ProductMapper
{
    /** @var  \Magento\Catalog\Model\ProductFactory */
    protected $productFactory;

    /** @var  \Magento\Catalog\Model\Product\Type */
    protected $productTypes;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Product\Type $productTypes
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Type $productTypes
    ) {
        $this->productFactory = $productFactory;
        $this->productTypes = $productTypes;
    }

    /**
     * @param  Product $product
     * @param  \Magento\Catalog\Model\Product $productModel
     * @param  string[] $customAttributesToSkip
     * @return \Magento\Catalog\Model\Product
     * @throws \RuntimeException
     */
    public function toModel(
        Product $product,
        \Magento\Catalog\Model\Product $productModel = null,
        $customAttributesToSkip = array()
    ) {
        /** @var \Magento\Catalog\Model\Product $productModel */
        $productModel = $productModel ? : $this->productFactory->create();
        $productModel->addData(ExtensibleDataObjectConverter::toFlatArray($product, $customAttributesToSkip));
        if (!is_numeric($productModel->getAttributeSetId())) {
            $productModel->setAttributeSetId($productModel->getDefaultAttributeSetId());
        }
        if (!$productModel->hasTypeId()) {
            $productModel->setTypeId(\Magento\Catalog\Model\Product\Type::DEFAULT_TYPE);
        } elseif (!isset($this->productTypes->getTypes()[$productModel->getTypeId()])) {
            throw new \RuntimeException('Illegal product type');
        }
        return $productModel;
    }
}
