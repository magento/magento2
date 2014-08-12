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
namespace Magento\Catalog\Service\V1\Category\ProductLinks;

use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Service\V1\Category\CategoryLoaderFactory;
use Magento\Catalog\Service\V1\Data\Category;
use Magento\Catalog\Service\V1\Data\Category\ProductLink;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\StateException;

class WriteService implements WriteServiceInterface
{
    /**
     * @var CategoryLoaderFactory
     */
    private $categoryLoaderFactory;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @param CategoryLoaderFactory $categoryLoaderFactory
     * @param ProductFactory $productFactory
     */
    public function __construct(
        CategoryLoaderFactory $categoryLoaderFactory,
        ProductFactory $productFactory
    ) {
        $this->categoryLoaderFactory = $categoryLoaderFactory;
        $this->productFactory = $productFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function assignProduct($categoryId, ProductLink $productLink)
    {
        return $this->setProductToCategory($categoryId, $productLink, true, 'Product already exists in this category');
    }

    /**
     * {@inheritdoc}
     */
    public function updateProduct($categoryId, ProductLink $productLink)
    {
        return $this->setProductToCategory($categoryId, $productLink, false, 'Product not found in this category');
    }

    /**
     * @param int $categoryId
     * @param ProductLink $productLink
     * @param bool $isInPositions
     * @param string $stateExceptionMessage
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function setProductToCategory($categoryId, ProductLink $productLink, $isInPositions, $stateExceptionMessage)
    {
        /** @var CategoryModel $category */
        $category = $this->categoryLoaderFactory->create()->load($categoryId);

        $productId = $this->productFactory->create()->getIdBySku($productLink->getSku());
        $productPositions = $category->getProductsPosition();

        if ($isInPositions === array_key_exists($productId, $productPositions)) {
            throw new StateException($stateExceptionMessage);
        }

        $newProductPositions = [$productId => $productLink->getPosition()] + $productPositions;

        $category->setPostedProducts($newProductPositions);
        try {
            $category->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                'Could not save product "%1" with position %2 to category %3',
                [
                    $productId,
                    $productLink->getPosition(),
                    $categoryId,
                ],
                $e
            );
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function removeProduct($categoryId, $productSku)
    {
        /** @var CategoryModel $category */
        $category = $this->categoryLoaderFactory->create()->load($categoryId);
        $productId = $this->productFactory->create()->getIdBySku($productSku);

        /**
         * old category-product relationships
         */
        $productPositions = $category->getProductsPosition();
        if (!array_key_exists($productId, $productPositions)) {
            throw new StateException('Category does not contain specified product');
        }
        unset($productPositions[$productId]);
        $category->setPostedProducts($productPositions);

        try {
            $category->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                'Could not remove product "%1" from category with ID "%2"',
                [
                    $productSku,
                    $categoryId,
                ],
                $e
            );
        }
        return true;
    }
}
