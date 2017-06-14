<?php
/**
 * Plugin for \Magento\Catalog\Api\ProductRepositoryInterface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Plugin\ProductRepository;

class TransactionWrapper
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $resourceModel;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product $resourceModel
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product $resourceModel
    ) {
        $this->resourceModel = $resourceModel;
    }

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $subject
     * @param callable $proceed
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param bool $saveOptions
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Closure $proceed,
        \Magento\Catalog\Api\Data\ProductInterface $product,
        $saveOptions = false
    ) {
        $this->resourceModel->beginTransaction();
        try {
            /** @var \Magento\Catalog\Api\Data\ProductInterface $result */
            $result = $proceed($product, $saveOptions);
            $this->resourceModel->commit();
            return $result;
        } catch (\Magento\Framework\Exception\AfterCommitException $e) {
            throw $e->getPrevious();
        } catch (\Exception $e) {
            $this->resourceModel->rollBack();
            throw $e;
        }
    }

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $subject
     * @param callable $proceed
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return bool
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDelete(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Closure $proceed,
        \Magento\Catalog\Api\Data\ProductInterface $product
    ) {
        $this->resourceModel->beginTransaction();
        try {
            /** @var bool $result */
            $result = $proceed($product);
            $this->resourceModel->commit();
            return $result;
        } catch (\Magento\Framework\Exception\AfterCommitException $e) {
            throw $e->getPrevious();
        } catch (\Exception $e) {
            $this->resourceModel->rollBack();
            throw $e;
        }
    }

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $subject
     * @param callable $proceed
     * @param string $productSku
     * @return bool
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDeleteById(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Closure $proceed,
        $productSku
    ) {
        $this->resourceModel->beginTransaction();
        try {
            /** @var bool $result */
            $result = $proceed($productSku);
            $this->resourceModel->commit();
            return $result;
        } catch (\Magento\Framework\Exception\AfterCommitException $e) {
            throw $e->getPrevious();
        } catch (\Exception $e) {
            $this->resourceModel->rollBack();
            throw $e;
        }
    }
}
