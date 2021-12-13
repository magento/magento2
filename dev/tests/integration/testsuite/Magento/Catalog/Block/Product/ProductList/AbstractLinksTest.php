<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\ProductList;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductLink\Link;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractLinks - abstract class when testing blocks of linked products
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractLinksTest extends TestCase
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var LayoutInterface */
    protected $layout;

    /** @var ProductInterface|Product */
    protected $product;

    /** @var ProductLinkInterfaceFactory */
    protected $productLinkInterfaceFactory;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var array */
    protected $existingProducts = [
        'wrong-simple' => [
            'position' => 1,
        ],
        'simple-249' => [
            'position' => 2,
        ],
        'simple-156' => [
            'position' => 3,
        ],
    ];

    /** @var AbstractProduct */
    protected $block;

    /** @var string */
    protected $linkType;

    /** @var string */
    protected $titleName;

    /** @var string */
    protected $titleXpath = "//strong[@id = 'block-%s-heading'][contains(text(), '%s')]";

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->productLinkInterfaceFactory = $this->objectManager->get(ProductLinkInterfaceFactory::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
    }

    /**
     * Provide test data to verify the display of linked products.
     *
     * @return array
     */
    public function displayLinkedProductsProvider(): array
    {
        return [
            'product_all_displayed' => [
                'data' => [
                    'updateProducts' => [],
                    'expectedProductLinks' => [
                        'wrong-simple',
                        'simple-249',
                        'simple-156',
                    ],
                ],
            ],
            'product_disabled' => [
                'data' => [
                    'updateProducts' => [
                        'wrong-simple' => ['status' => Status::STATUS_DISABLED],
                    ],
                    'expectedProductLinks' => [
                        'simple-249',
                        'simple-156',
                    ],
                ],
            ],
            'product_invisibility' => [
                'data' => [
                    'updateProducts' => [
                        'simple-249' => ['visibility' => Visibility::VISIBILITY_NOT_VISIBLE],
                    ],
                    'expectedProductLinks' => [
                        'wrong-simple',
                        'simple-156',
                    ],
                ],
            ],
            'product_invisible_in_catalog' => [
                'data' => [
                    'updateProducts' => [
                        'simple-249' => ['visibility' => Visibility::VISIBILITY_IN_SEARCH],
                    ],
                    'expectedProductLinks' => [
                        'wrong-simple',
                        'simple-156',
                    ],
                ],
            ],
            'product_out_of_stock' => [
                'data' => [
                    'updateProducts' => [
                        'simple-156' => [
                            'stock_data' => [
                                'use_config_manage_stock'   => 1,
                                'qty'                       => 0,
                                'is_qty_decimal'            => 0,
                                'is_in_stock'               => 0,
                            ],
                        ],
                    ],
                    'expectedProductLinks' => [
                        'wrong-simple',
                        'simple-249',
                    ],
                ],
            ],
        ];
    }

    /**
     * Provide test data to verify the display of linked products on different websites.
     *
     * @return array
     */
    public function multipleWebsitesLinkedProductsProvider(): array
    {
        return [
            'first_website' => [
                'data' => [
                    'storeCode' => 'default',
                    'productLinks' => [
                        'simple-2' => ['position' => 4],
                    ],
                    'expectedProductLinks' => [
                        'wrong-simple',
                        'simple-2',
                    ],
                ],
            ],
            'second_website' => [
                'data' => [
                    'storeCode' => 'fixture_second_store',
                    'productLinks' => [
                        'simple-2' => ['position' => 4],
                    ],
                    'expectedProductLinks' => [
                        'simple-249',
                        'simple-2',
                    ],
                ],
            ],
        ];
    }

    /**
     * Get test data to check position of related, up-sells and cross-sells products
     *
     * @return array
     */
    protected function getPositionData(): array
    {
        return [
            'productLinks' => array_replace_recursive(
                $this->existingProducts,
                [
                    'wrong-simple' => ['position' => 2],
                    'simple-249' => ['position' => 3],
                    'simple-156' => ['position' => 1],
                ]
            ),
            'expectedProductLinks' => [
                'simple-156',
                'wrong-simple',
                'simple-249',
            ],
        ];
    }

    /**
     * Prepare a block of linked products
     *
     * @return void
     */
    protected function prepareBlock(): void
    {
        $this->block->setLayout($this->layout);
        $this->block->setTemplate('Magento_Catalog::product/list/items.phtml');
        $this->block->setType($this->linkType);
    }

    /**
     * Set linked products by link type for current product received from array
     *
     * @param ProductInterface $product
     * @param array $productData
     * @return void
     */
    private function setCustomProductLinks(ProductInterface $product, array $productData): void
    {
        $productLinks = [];
        foreach ($productData as $sku => $data) {
            /** @var ProductLinkInterface|Link $productLink */
            $productLink = $this->productLinkInterfaceFactory->create();
            $productLink->setSku($product->getSku());
            $productLink->setLinkedProductSku($sku);
            if (isset($data['position'])) {
                $productLink->setPosition($data['position']);
            }
            $productLink->setLinkType($this->linkType);
            $productLinks[] = $productLink;
        }
        $product->setProductLinks($productLinks);
    }

    /**
     * Update product attributes
     *
     * @param array $products
     * @return void
     */
    protected function updateProducts(array $products): void
    {
        foreach ($products as $sku => $data) {
            /** @var ProductInterface|Product $product */
            $product = $this->productRepository->get($sku);
            $product->addData($data);
            $this->productRepository->save($product);
        }
    }

    /**
     * Get an array of received linked products
     *
     * @param array $items
     * @return array
     */
    protected function getActualLinks(array $items): array
    {
        $actualLinks = [];
        /** @var ProductInterface $productItem */
        foreach ($items as $productItem) {
            $actualLinks[] = $productItem->getSku();
        }

        return $actualLinks;
    }

    /**
     * Link products to an existing product
     *
     * @param string $sku
     * @param array $productLinks
     * @return void
     */
    protected function linkProducts(string $sku, array $productLinks): void
    {
        $product = $this->productRepository->get($sku);
        $this->setCustomProductLinks($product, $productLinks);
        $this->productRepository->save($product);
    }

    /**
     * Prepare the necessary websites for all products
     *
     * @return array
     */
    protected function prepareProductsWebsiteIds(): array
    {
        $websiteId = $this->storeManager->getWebsite('test')->getId();
        $defaultWebsiteId = $this->storeManager->getWebsite('base')->getId();

        return [
            'simple-1' => [
                'website_ids' => [$defaultWebsiteId, $websiteId],
            ],
            'simple-2' => [
                'website_ids' => [$defaultWebsiteId, $websiteId],
            ],
            'wrong-simple' => [
                'website_ids' => [$defaultWebsiteId],
            ],
            'simple-249' => [
                'website_ids' => [$websiteId],
            ],
            'simple-156' => [
                'website_ids' => [],
            ],
        ];
    }
}
