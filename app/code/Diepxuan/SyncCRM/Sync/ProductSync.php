<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2025-04-02 17:05:37
 */

namespace Diepxuan\SyncCRM\Sync;

use Diepxuan\SyncCRM\Helper\Config;
use Diepxuan\SyncCRM\Helper\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;

class ProductSync extends CrmSync
{
    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StockItemRepositoryInterface
     */
    protected $stockItemRepository;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    protected State $appState;
    protected $categoryCollectionFactory;

    public function __construct(
        Context $context,
        Config $config,
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        StockItemRepositoryInterface $stockItemRepository,
        StockRegistryInterface $stockRegistry,
        State $appState,
        CollectionFactory $categoryCollectionFactory
    ) {
        parent::__construct($context, $config);
        $this->productFactory            = $productFactory;
        $this->productRepository         = $productRepository;
        $this->stockItemRepository       = $stockItemRepository;
        $this->stockRegistry             = $stockRegistry;
        $this->appState                  = $appState;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $categoryCollection              = $this->categoryCollectionFactory->create();
    }

    public function sync(): void
    {
        try {
            // Lấy danh sách sản phẩm từ CRM
            $products = $this->fetch('products');
            $this->appState->setAreaCode(Area::AREA_ADMINHTML);

            $iError = 0;

            foreach ($products as $productData) {
                $sku      = $productData['ma_vt'];
                $quantity = (int) max((float) $productData['quantity'], 0);
                $ksd      = (bool) $productData['ksd'];
                $status   = !$ksd && $quantity > 0;
                $urlKey   = strtolower(str_replace(' ', '-', strtolower(vn_convert_encoding($productData['ten_vt']))));

                $category = $this->findCategoryBySimbaCategory($productData['ma_nhvt']);
                $catIds   = $category ? $category->getPath() : '1/2';
                $catIds   = explode('/', $catIds);
                $catIds   = array_filter($catIds, static fn ($id) => 1 !== $id);
                // dd($productData);

                try {
                    $product = $this->productRepository->get($sku);
                } catch (NoSuchEntityException $e) {
                    $product = $this->productFactory->create();
                    $product->setSku($sku);
                }

                $product->setName($productData['ten_vt']);
                $product->setPrice($productData['gia_nt2']);
                $product->setTypeId('simple');
                $product->setAttributeSetId(4);
                $product->setStatus($product->getStatus() && $status);
                $product->setVisibility(4);
                $product->setCategoryIds(array_merge($product->getCategoryIds() ?: [], $catIds));

                $product->setCustomAttribute('meta_title', $product->getName());
                $product->setCustomAttribute('meta_keyword', $product->getName());
                $product->setCustomAttribute('meta_description', $product->getName());
                $product->setCustomAttribute('url_key', $urlKey);
                $product->setCustomAttribute('status', $status);

                try {
                    // $product->save();
                    $this->productRepository->save($product);
                } catch (\Exception $e) {
                    printf(
                        __(
                            "Error saving product [%3] %1 - %2\n",
                            $product->getName(),
                            $e->getMessage(),
                            $iError
                        )->__toString()
                    );
                }

                $stockItem = $this->stockRegistry->getStockItemBySku($sku);
                $stockItem->setQty($quantity);
                $stockItem->setIsInStock($quantity > 0 ? 1 : 0);
                $this->stockItemRepository->save($stockItem);

                ++$iError;
                // printf(<<<EOF
                //     {$iError} {$product->getStatus()} [{$ksd}|{$quantity}] {$product->getName()}
                //             SKU: {$product->getSku()}
                //             Price: {$product->getPrice()}
                //             Quantity: {$quantity}

                //     EOF);
            }
        } catch (\Exception $e) {
            $this->getLogger()->error('❌ Lỗi đồng bộ sản phẩm: ' . $e->getMessage());
            printf("Lỗi đồng bộ sản phẩm: %s\n", $e->getMessage());
        }
    }

    public function findCategoryBySimbaCategory($simbaCategoryValue)
    {
        // Lấy category collection
        $categoryCollection = $this->categoryCollectionFactory->create();

        // Lọc theo custom attribute simba_category
        $categoryCollection->addAttributeToSelect('*')
            ->addAttributeToFilter('simba_category', $simbaCategoryValue)
        ;

        $categories = $categoryCollection->getItems();

        return reset($categories) ?: null;
    }
}
