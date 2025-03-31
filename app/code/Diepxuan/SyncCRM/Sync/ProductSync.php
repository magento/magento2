<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2025-03-31 17:42:13
 */

namespace Diepxuan\SyncCRM\Sync;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class ProductSync
{
    protected $productFactory;
    protected $resource;
    protected $logger;
    protected $connection;

    public function __construct(ProductFactory $productFactory, ResourceConnection $resource, LoggerInterface $logger)
    {
        $this->productFactory = $productFactory;
        $this->resource       = $resource;
        $this->logger         = $logger;
        $this->connection     = $this->resource->getConnection('sqlsrv');
    }

    public function sync(): void
    {
        try {
            // Lấy danh sách sản phẩm từ CRM
            $query    = "SELECT ma_vt AS sku, ten_vt AS name, gia_nt0 AS price FROM InDmVt WHERE ma_cty = '001'";
            $products = $this->connection->fetchAll($query);

            foreach ($products as $productData) {
                echo $sku;
                $product = $this->productFactory->create();
                // $product->setSku($productData['sku']);
                // $product->setName($productData['name']);
                // $product->setPrice($productData['price']);
                // $product->setTypeId('simple');
                // $product->setAttributeSetId(4);
                // $product->setStatus(1);
                // $product->setVisibility(4);
                // $product->save();
            }

            $this->logger->info('✅ Đồng bộ sản phẩm từ CRM thành công.');
            printf("Đồng bộ sản phẩm từ CRM thành công.\n");
        } catch (\Exception $e) {
            $this->logger->error('❌ Lỗi đồng bộ sản phẩm: ' . $e->getMessage());
            printf("Lỗi đồng bộ sản phẩm: %s\n", $e->getMessage());
        }
    }
}
