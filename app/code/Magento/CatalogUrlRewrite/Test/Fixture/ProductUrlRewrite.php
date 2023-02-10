<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Fixture;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewrite as UrlRewriteResourceModel;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewriteDataModel;
use Magento\UrlRewrite\Test\Fixture\UrlRewrite;

class ProductUrlRewrite extends UrlRewrite
{
    private const DEFAULT_DATA = [
        UrlRewriteDataModel::ENTITY_TYPE => 'category',
        UrlRewriteDataModel::REDIRECT_TYPE => 0,
        UrlRewriteDataModel::STORE_ID => 1
    ];

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var ProductUrlPathGenerator
     */
    private ProductUrlPathGenerator $productUrlPathGenerator;

    /**
     * @var UrlFinderInterface
     */
    private UrlFinderInterface $urlFinder;

    /**
     * @inheritDoc
     */
    public function __construct(
        UrlRewriteFactory $urlRewriteFactory,
        UrlRewriteResourceModel $urlRewriteResourceModel,
        ProcessorInterface $dataProcessor,
        ProductRepositoryInterface $productRepository,
        ProductUrlPathGenerator $productUrlPathGenerator,
        UrlFinderInterface $urlFinder
    ) {
        parent::__construct($urlRewriteFactory, $urlRewriteResourceModel, $dataProcessor);
        $this->productRepository = $productRepository;
        $this->productUrlPathGenerator = $productUrlPathGenerator;
        $this->urlFinder = $urlFinder;
    }

    /**
     * @inheritDoc
     */
    public function apply(array $data = []): ?DataObject
    {
        return parent::apply($this->prepareData($data));
    }

    /**
     * Prepare default data
     *
     * @param array $data
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function prepareData(array $data): array
    {
        $data = array_merge(self::DEFAULT_DATA, $data);
        $product = $this->productRepository->getById(
            $data[UrlRewriteDataModel::ENTITY_ID],
            storeId: $data[UrlRewriteDataModel::STORE_ID]
        );
        if (!isset($data[UrlRewriteDataModel::TARGET_PATH])) {
            $data[UrlRewriteDataModel::TARGET_PATH] = $this->productUrlPathGenerator->getCanonicalUrlPath($product);
            if ($data[UrlRewriteDataModel::REDIRECT_TYPE]) {
                $rewrite = $this->urlFinder->findOneByData(
                    [
                        UrlRewriteDataModel::ENTITY_ID => $data[UrlRewriteDataModel::ENTITY_ID],
                        UrlRewriteDataModel::TARGET_PATH => $data[UrlRewriteDataModel::TARGET_PATH],
                        UrlRewriteDataModel::ENTITY_TYPE => $data[UrlRewriteDataModel::ENTITY_TYPE],
                        UrlRewriteDataModel::STORE_ID => $data[UrlRewriteDataModel::STORE_ID],
                    ]
                );
                if ($rewrite) {
                    $data[UrlRewriteDataModel::TARGET_PATH] = $rewrite->getRequestPath();
                } else {
                    $data[UrlRewriteDataModel::TARGET_PATH] = $this->productUrlPathGenerator->getUrlPath($product);
                }
            }
        }
        return $data;
    }
}
