<?php
/**
 * Product initialzation helper
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
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

class Configurable
{
    /**
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productType
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productType,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->productType = $productType;
        $this->request = $request;
    }

    /**
     * Initialize data for configurable product
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterInitialize(
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject,
        \Magento\Catalog\Model\Product $product
    ) {
        $attributes = $this->request->getParam('attributes');
        if (!empty($attributes)) {
            $this->productType->setUsedProductAttributeIds($attributes, $product);

            $product->setNewVariationsAttributeSetId($this->request->getPost('new-variations-attribute-set-id'));
            $associatedProductIds = $this->request->getPost('associated_product_ids', array());
            $variationsMatrix = $this->request->getParam('variations-matrix', array());
            if (!empty($variationsMatrix)) {
                $generatedProductIds = $this->productType->generateSimpleProducts($product, $variationsMatrix);
                $associatedProductIds = array_merge($associatedProductIds, $generatedProductIds);
            }
            $product->setAssociatedProductIds(array_filter($associatedProductIds));

            $product->setCanSaveConfigurableAttributes(
                (bool)$this->request->getPost('affect_configurable_product_attributes')
            );
        }

        return $product;
    }
}
