<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2025-04-01 19:32:34
 */

namespace Diepxuan\SyncCRM\Sync;

use Diepxuan\SyncCRM\Helper\Config;
use Diepxuan\SyncCRM\Helper\Context;
use Magento\Catalog\Model\CategoryFactory;

class CategorySync extends CrmSync
{
    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    public function __construct(
        Context $context,
        Config $config,
        CategoryFactory $categoryFactory
    ) {
        parent::__construct($context, $config);
        $this->categoryFactory = $categoryFactory;
    }

    public function sync(): void
    {
        try {
            // Lấy danh sách danh mục từ CRM
            $categories = $this->fetch('categories');
            dd($categories);

            foreach ($products as $productData) {
                echo $productData['sku'];
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

            $this->getLogger()->info('✅ Đồng bộ sản phẩm từ CRM thành công.');
            printf("Đồng bộ sản phẩm từ CRM thành công.\n");
        } catch (\Exception $e) {
            $this->getLogger()->error('❌ Lỗi đồng bộ sản phẩm: ' . $e->getMessage());
            printf("Lỗi đồng bộ sản phẩm: %s\n", $e->getMessage());
        }
    }
}
