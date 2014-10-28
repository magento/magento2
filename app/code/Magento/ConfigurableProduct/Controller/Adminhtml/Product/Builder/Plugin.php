<?php
/**
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
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Builder;

use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\Product\Type;

class Plugin
{
    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurableType;

    /**
     * @param ProductFactory $productFactory
     * @param Type\Configurable $configurableType
     */
    public function __construct(ProductFactory $productFactory, Type\Configurable $configurableType)
    {
        $this->productFactory = $productFactory;
        $this->configurableType = $configurableType;
    }

    /**
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function aroundBuild(
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $product = $proceed($request);

        if ($request->has('attributes')) {
            $attributes = $request->getParam('attributes');
            if (!empty($attributes)) {
                $product->setTypeId(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);
                $this->configurableType->setUsedProductAttributeIds($attributes, $product);
            } else {
                $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
            }
        }

        // Required attributes of simple product for configurable creation
        if ($request->getParam('popup') && ($requiredAttributes = $request->getParam('required'))) {
            $requiredAttributes = explode(",", $requiredAttributes);
            foreach ($product->getAttributes() as $attribute) {
                if (in_array($attribute->getId(), $requiredAttributes)) {
                    $attribute->setIsRequired(1);
                }
            }
        }

        if ($request->getParam('popup')
            && $request->getParam('product')
            && !is_array($request->getParam('product'))
            && $request->getParam('id', false) === false
        ) {
            $configProduct = $this->productFactory->create();
            $configProduct->setStoreId(0)->load($request->getParam('product'))->setTypeId($request->getParam('type'));

            $data = array();
            foreach ($configProduct->getTypeInstance()->getEditableAttributes($configProduct) as $attribute) {
                /* @var $attribute \Magento\Catalog\Model\Resource\Eav\Attribute */
                if (!$attribute->getIsUnique() &&
                    $attribute->getFrontend()->getInputType() != 'gallery' &&
                    $attribute->getAttributeCode() != 'required_options' &&
                    $attribute->getAttributeCode() != 'has_options' &&
                    $attribute->getAttributeCode() != $configProduct->getIdFieldName()
                ) {
                    $data[$attribute->getAttributeCode()] = $configProduct->getData($attribute->getAttributeCode());
                }
            }
            $product->addData($data);
            $product->setWebsiteIds($configProduct->getWebsiteIds());
        }

        return $product;
    }
}
