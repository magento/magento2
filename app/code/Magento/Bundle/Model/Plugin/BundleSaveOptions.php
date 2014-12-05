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

namespace Magento\Bundle\Model\Plugin;

class BundleSaveOptions
{
    /**
     * @var \Magento\Bundle\Api\ProductOptionRepositoryInterface
     */
    protected $optionRepository;

    /**
     * @param \Magento\Bundle\Api\ProductOptionRepositoryInterface $optionRepository
     */
    public function __construct(\Magento\Bundle\Api\ProductOptionRepositoryInterface $optionRepository)
    {
        $this->optionRepository = $optionRepository;
    }

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $subject
     * @param callable $proceed
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param bool $saveOptions
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Closure $proceed,
        \Magento\Catalog\Api\Data\ProductInterface $product,
        $saveOptions = false
    ) {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $result */
        $result = $proceed($product, $saveOptions);

        if ($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            return $result;
        }

        /* @var \Magento\Framework\Api\AttributeValue $bundleProductOptionsAttrValue */
        $bundleProductOptionsAttrValue = $product->getCustomAttribute('bundle_product_options');
        if (is_null($bundleProductOptionsAttrValue) || !is_array($bundleProductOptionsAttrValue->getValue())) {
            $bundleProductOptions = [];
        } else {
            $bundleProductOptions = $bundleProductOptionsAttrValue->getValue();
        }

        if (is_array($bundleProductOptions)) {
            foreach ($bundleProductOptions as $option) {
                $this->optionRepository->save($result, $option);
            }
        }
        return $result;
    }
}
