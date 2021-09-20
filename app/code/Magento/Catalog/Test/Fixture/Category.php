<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Fixture;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

/**
 * Creates category fixture
 */
class Category implements RevertibleDataFixtureInterface
{
    private const DEFAULT_PARENT_ID = 2;

    private const DEFAULT_PARENT_PATH = '1/2';

    private const DEFAULT_DATA = [
        'name' => 'Category%uniqid%',
        'parent_id' => self::DEFAULT_PARENT_ID,
        'is_active' => true,
        'position' => 1,
        'available_sort_by' => ['position', 'name'],
        'default_sort_by' => 'name'
    ];

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var CategoryResource
     */
    private $categoryResource;

    /**
     * @var ProcessorInterface
     */
    private $dataProcessor;

    /**
     * @param CategoryFactory $categoryFactory
     * @param CategoryResource $categoryResource
     * @param ProcessorInterface $dataProcessor
     */
    public function __construct(
        CategoryFactory $categoryFactory,
        CategoryResource $categoryResource,
        ProcessorInterface $dataProcessor
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryResource = $categoryResource;
        $this->dataProcessor = $dataProcessor;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?array
    {
        $data = $this->prepareData($data);
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $this->categoryFactory->create();
        $category->isObjectNew(true);
        $category->setData($data);
        if (isset($data['id'])) {
            $category->setId($data['id']);
        }
        $this->categoryResource->save($category);

        return [
            'category' => $category
        ];
    }

    /**
     * Prepare category data
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $data = $this->dataProcessor->process($this, array_merge(self::DEFAULT_DATA, $data));
        if (!isset($data['path'])) {
            $data['path'] = self::DEFAULT_PARENT_PATH;
            if ((int) $data['parent_id'] !== self::DEFAULT_PARENT_ID) {
                /** @var \Magento\Catalog\Model\Category $parentCategory */
                $parentCategory = $this->categoryFactory->create();
                $this->categoryResource->load($parentCategory, $data['parent_id']);
                $data['path'] = $parentCategory->getPath();
            }
            if (isset($data['id'])) {
                $data['path'] .= '/' . $data['id'];
            }
        }
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function revert(array $data = []): void
    {
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $this->categoryFactory->create();
        $this->categoryResource->load($category, $data['category']->getId());
        if ($category->getId()) {
            $this->categoryResource->delete($category);
        }
    }
}
