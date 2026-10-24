<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewriteGraphQl\Model\DataProvider\UrlRewrite;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\UrlRewriteGraphQl\Model\DataProvider\EntityDataProviderInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

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
     * @throws GraphQlNoSuchEntityException|NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData(
        string $entity_type,
        int $id,
        ?ResolveInfo $info = null,
        ?int $storeId = null
    ): array {
        $product = $this->productRepository->getById($id, false, $storeId);
        $result = $product->getData();

        if ((int)$product->getStatus() !== Status::STATUS_ENABLED) {
            throw new GraphQlNoSuchEntityException(__('This product is disabled.'));
        }
        $result['model'] = $product;
        return $result;
    }
}
