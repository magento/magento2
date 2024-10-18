<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewriteGraphQl\Model\DataProvider\UrlRewrite;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\UrlRewriteGraphQl\Model\DataProvider\EntityDataProviderInterface;

class ProductDataProvider implements EntityDataProviderInterface
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @param ProductRepository $productRepository
     */
    public function __construct(
        ProductRepository $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * Get catalog tree data
     *
     * @param string $entity_type
     * @param int $id
     * @param ResolveInfo|null $info
     * @param int|null $storeId
     * @return array
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData(
        string $entity_type,
        int $id,
        ResolveInfo $info = null,
        int $storeId = null
    ): array {
        $product = $this->productRepository->getById($id, false, $storeId);
        $result = $product->getData();
        $result['model'] = $product;
        return $result;
    }
}
