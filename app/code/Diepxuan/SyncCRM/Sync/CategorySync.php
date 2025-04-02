<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2025-04-02 13:45:03
 */

namespace Diepxuan\SyncCRM\Sync;

use Diepxuan\SyncCRM\Helper\Config;
use Diepxuan\SyncCRM\Helper\Context;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Filter\FilterManager;

class CategorySync extends CrmSync
{
    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryCollectionFactory;

    /**
     * @var FilterManager
     */
    protected $filterManager;

    public function __construct(
        Context $context,
        Config $config,
        CategoryRepositoryInterface $categoryRepository,
        CategoryInterfaceFactory $categoryFactory,
        CollectionFactory $categoryCollectionFactory,
        FilterManager $filterManager
    ) {
        parent::__construct($context, $config);
        $this->categoryRepository        = $categoryRepository;
        $this->categoryFactory           = $categoryFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->filterManager             = $filterManager;
    }

    public function sync(): void
    {
        try {
            $categories = $this->fetch('categories');

            foreach ($categories as $categoryData) {
                if ('PRODUCT' === $categoryData['ma_nhvt']) {
                    continue;
                }

                $category = $this->getCategoryByName($categoryData['ten_nhvt']);
                if (!$category) {
                    $category = $this->categoryFactory->create();
                }

                $categorySlug     = strtolower(vn_convert_encoding($categoryData['ma_nhvt']));
                $categoryParentId = $this->getCategoryParentId($categoryData, $categories);
                $category->setName($categoryData['ten_nhvt']);
                $category->setIsActive($category->getIsActive());
                $category->setIncludeInMenu($category->getIncludeInMenu());
                $category->setParentId($categoryParentId);
                $category->setPath($this->getCategoryPath($categoryData, $categories));
                // $category->setPath("1/2/{$categoryId}");
                // $category->setParentId($categoryData['parent_id'] ?? 2);
                // $category->setPath("1/{$categoryData['parent_id']}/{$categoryData['id']}");

                $category->setCustomAttribute('simba_category', $categorySlug);

                $this->categoryRepository->save($category);
            }
        } catch (\Exception $e) {
            $this->getLogger()->error('❌ Lỗi đồng bộ sản phẩm: ' . $e->getMessage());
            // printf("Lỗi đồng bộ sản phẩm: %s\n", $e->getMessage());
        }
    }

    private function getCategoryByName($name)
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToFilter('name', $name);
        $collection->setPageSize(1);

        return $collection->getFirstItem()->getId() ? $collection->getFirstItem() : null;
    }

    private function getCategoryParentId($category, $categories)
    {
        try {
            $nhom_me        = $category['nhom_me'];
            $parentCategory = array_filter($categories, static fn ($category) => $category['ma_nhvt'] === $nhom_me);
            $parentCategory = reset($parentCategory);
            $parentCategory = $this->getCategoryByName($parentCategory['ten_nhvt']);

            return $parentCategory ? $parentCategory->getId() : 2;
        } catch (\Exception $e) {
            return 2;
        }
    }

    private function getCategoryPath($category, $categories)
    {
        $path = [];

        while ($category) {
            $path[] = $category['ten_nhvt'];

            $category = array_filter($categories, static fn ($cat) => $cat['ma_nhvt'] === $category['nhom_me']);
            $category = reset($category) ?: null;
        }
        $path[] = 1;

        foreach ($path as $i => $ten_nhvt) {
            if ('Hang Hoa' === $ten_nhvt) {
                $path[$i] = 2;

                continue;
            }
            $category = $this->getCategoryByName($ten_nhvt);
            if ($category) {
                $path[$i] = $category->getId();
            }
        }

        return implode('/', array_reverse($path));
    }
}
