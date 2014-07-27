<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Service\V1\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Service\V1\Data\Category as CategoryDataObject;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Service\V1\Data\Category\Mapper as CategoryMapper;
use Magento\Store\Model\StoreManagerInterface;

class WriteService implements WriteServiceInterface
{
    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var CategoryMapper
     */
    private $categoryMapper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * List of fields that can used config values in case when value does not defined directly
     *
     * @var array
     */
    private $useConfigFields = ['available_sort_by', 'default_sort_by', 'filter_price_range'];

    /**
     * @param CategoryFactory $categoryFactory
     * @param CategoryMapper $categoryMapper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CategoryFactory $categoryFactory,
        CategoryMapper $categoryMapper,
        StoreManagerInterface $storeManager
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryMapper = $categoryMapper;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function create(CategoryDataObject $category)
    {
        try {
            $categoryModel = $this->categoryMapper->toModel($category);
            $parentId = $category->getParentId() ?: $this->storeManager->getStore()->getRootCategoryId();
            $parentCategory = $this->categoryFactory->create()->load($parentId);
            $categoryModel->setPath($parentCategory->getPath());

            $this->validateCategory($categoryModel);
            $categoryModel->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Could not save category: %1', [$e->getMessage()], $e);
        }
        return $categoryModel->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function delete($categoryId)
    {
        /** @var Category $category */
        $category = $this->loadCategory($categoryId);

        try {
            $category->delete();
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Cannot delete category with id %1', [$categoryId], $e);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function update($categoryId, CategoryDataObject $category)
    {
        $model = $this->loadCategory($categoryId);
        try {
            $this->categoryMapper->toModel($category, $model);
            $this->validateCategory($model);
            $model->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Could not save category', [], $e);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function move($categoryId, $parentId, $afterId = null)
    {
        $model = $this->loadCategory($categoryId);
        $parentCategory = $this->loadCategory($parentId);

        if (is_null($afterId) && $parentCategory->hasChildren()) {
            $parentChildren = $parentCategory->getChildren();
            $categoryIds = explode(',', $parentChildren);
            $afterId = array_pop($categoryIds);
        }

        if (strpos($parentCategory->getPath(), $model->getPath()) === 0) {
            throw new \Magento\Framework\Model\Exception(
                "Operation do not allow to move a parent category to any of children category"
            );
        }
        try {
            $model->move($parentId, $afterId);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Model\Exception('Could not move category');
        }
        return true;
    }

    /**
     * Validate category process
     *
     * @param  Category $category
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    protected function validateCategory(Category $category)
    {
        $useConfigFields = [];
        foreach ($this->useConfigFields as $field) {
            if (!$category->getData($field)) {
                $useConfigFields[] = $field;
            }
        }
        $category->setData('use_post_data_config', $useConfigFields);
        $validate = $category->validate();
        if ($validate !== true) {
            foreach ($validate as $code => $error) {
                if ($error === true) {
                    $attribute = $category->getResource()->getAttribute($code)->getFrontend()->getLabel();
                    throw new \Magento\Framework\Model\Exception(__('Attribute "%1" is required.', $attribute));
                } else {
                    throw new \Magento\Framework\Model\Exception($error);
                }
            }
        }
        $category->unsetData('use_post_data_config');
    }

    /**
     * Load category
     *
     * @param int $id
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return \Magento\Catalog\Model\Category
     */
    protected function loadCategory($id)
    {
        $model = $this->categoryFactory->create();
        $model->load($id);
        if (!$model->getId()) {
            throw NoSuchEntityException::singleField(CategoryDataObject::ID, $id);
        }
        return $model;
    }
}
