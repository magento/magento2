<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\PageCache\Model\Spi\PageCacheTagsPreprocessorInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Add product identities to "noroute" page
 *
 * Ensure that "noroute" page has necessary product tags
 * so it can be invalidated once the product becomes visible again
 */
class ProductNotFoundPageCacheTags implements PageCacheTagsPreprocessorInterface
{
    private const NOROUTE_ACTION_NAME = 'cms_noroute_index';
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        RequestInterface $request,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->request = $request;
    }

    /**
     * @inheritDoc
     */
    public function process(array $tags): array
    {
        if ($this->request->getFullActionName() === self::NOROUTE_ACTION_NAME) {
            try {
                $productId = (int) $this->request->getParam('id');
                $product = $this->productRepository->getById(
                    $productId,
                    false,
                    $this->storeManager->getStore()->getId()
                );
            } catch (NoSuchEntityException $e) {
                $product = null;
            }
            if ($product) {
                $tags = array_merge($tags, $product->getIdentities());
            }
        }
        return $tags;
    }
}
