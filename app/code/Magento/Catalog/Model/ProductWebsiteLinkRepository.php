<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Catalog\Api\Data\ProductWebsiteLinkInterface;

/**
 * Class \Magento\Catalog\Model\ProductWebsiteLinkRepository
 *
 * @since 2.0.0
 */
class ProductWebsiteLinkRepository implements \Magento\Catalog\Api\ProductWebsiteLinkRepositoryInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     * @since 2.0.0
     */
    protected $productRepository;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function save(ProductWebsiteLinkInterface $productWebsiteLink)
    {
        if (!$productWebsiteLink->getWebsiteId()) {
            throw new InputException(__('There are not websites for assign to product'));
        }
        $product = $this->productRepository->get($productWebsiteLink->getSku());
        $product->setWebsiteIds(array_merge($product->getWebsiteIds(), [$productWebsiteLink->getWebsiteId()]));
        try {
            $this->productRepository->save($product);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not assign product "%1" to websites "%2"',
                    $product->getId(),
                    $productWebsiteLink->getWebsiteId()
                ),
                $e
            );
        }
        return true;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function delete(ProductWebsiteLinkInterface $productLink)
    {
        return $this->deleteById($productLink->getSku(), $productLink->getSku());
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function deleteById($sku, $websiteId)
    {
        $product = $this->productRepository->get($sku);
        $product->setWebsiteIds(array_diff($product->getWebsiteIds(), [$websiteId]));

        try {
            $this->productRepository->save($product);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __(
                    'Could not save product "%1" with websites %2',
                    $product->getId(),
                    implode(', ', $product->getWebsiteIds())
                ),
                $e
            );
        }
        return true;
    }
}
