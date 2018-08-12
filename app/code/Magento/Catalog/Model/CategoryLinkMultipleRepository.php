<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\CouldNotSaveException;

class CategoryLinkMultipleRepository implements \Magento\Catalog\Api\CategoryLinkMultipleRepositoryInterface
{
    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var ResourceModel\Product
     */
    private $productResourceModel;

    /**
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param ResourceModel\Product $productResourceModel
     */
    public function __construct(
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\ResourceModel\Product $productResourceModel
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productResourceModel = $productResourceModel;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function replace($categoryId, $productLinks)
    {
        $messages = [];
        try {
            $category = $this->categoryRepository->get($categoryId);
            $productPositions = [];
            foreach ($productLinks as $productLink) {
                try {
                    $productId = $this->productResourceModel->getIdBySku($productLink->getSku());
                    if ($productId) {
                        $productPositions[$productId] = $productLink->getPosition();
                    }
                } catch (NoSuchEntityException $e) {
                    $messages[] = "Product with SKU: " . $productLink->getSku() . ", does not exist.";
                }
            }
            $category->setPostedProducts($productPositions);
            try {
                $category->save();
            } catch (\Exception $e) {
                throw new CouldNotSaveException(
                    __(
                        'Could not save products to category %1',
                        $category->getId()
                    ),
                    $e
                );
            }
        } catch (NoSuchEntityException $e) {
            $messages[] = "Category with ID: " . $categoryId . ", does not exist.";
        }
        if (!empty($messages)) {
            return $messages;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function saveMultiple($categoryId, $productLinks)
    {
        $messages = [];
        try {
            $category = $this->categoryRepository->get($categoryId);
            $productPositions = $category->getProductsPosition();
            foreach ($productLinks as $productLink) {
                try {
                    $productId = $this->productResourceModel->getIdBySku($productLink->getSku());
                    if ($productId) {
                        $productPositions[$productId] = $productLink->getPosition();
                    }
                } catch (NoSuchEntityException $e) {
                    $messages[] = "Product with SKU: " . $productLink->getSku() . ", does not exist.";
                }
            }
            $category->setPostedProducts($productPositions);
            try {
                $category->save();
            } catch (\Exception $e) {
                throw new CouldNotSaveException(
                    __(
                        'Could not save products to category %1',
                        $category->getId()
                    ),
                    $e
                );
            }
        } catch (NoSuchEntityException $e) {
            $messages[] = "Category with ID: " . $categoryId . ", does not exist.";
        }
        if (!empty($messages)) {
            return $messages;
        }
        return true;
    }
}
