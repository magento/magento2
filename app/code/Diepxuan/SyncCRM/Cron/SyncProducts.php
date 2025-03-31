<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2025-03-31 13:02:18
 */

namespace Diepxuan\SyncCRM\Cron;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class SyncProducts
{
    protected $productFactory;
    protected $resource;
    protected $logger;

    public function __construct(ProductFactory $productFactory, ResourceConnection $resource, LoggerInterface $logger)
    {
        $this->productFactory = $productFactory;
        $this->resource       = $resource;
        $this->logger         = $logger;
    }

    public function execute(): void
    {
        try {
            $connection = $this->resource->getConnection('custom_sqlsrv'); // Kết nối SQL Server

            // Lấy danh sách sản phẩm từ CRM
            $query    = "SELECT ma_vt AS sku, ten_vt AS name, gia_nt0 AS price FROM InDmVt WHERE ma_cty = '001'";
            $products = $connection->fetchAll($query);

            foreach ($products as $productData) {
                $product = $this->productFactory->create();
                $product->setSku($productData['sku']);
                $product->setName($productData['name']);
                $product->setPrice($productData['price']);
                $product->setTypeId('simple');
                $product->setAttributeSetId(4);
                $product->setStatus(1);
                $product->setVisibility(4);
                $product->save();
            }

            $this->logger->info('✅ Đồng bộ sản phẩm từ CRM thành công.');
        } catch (\Exception $e) {
            $this->logger->error('❌ Lỗi đồng bộ sản phẩm: ' . $e->getMessage());
        }
    }
}
