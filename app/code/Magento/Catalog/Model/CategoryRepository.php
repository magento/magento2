<?php
/**
 *
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

namespace Magento\Catalog\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\StateException;

class CategoryRepository implements \Magento\Catalog\Api\CategoryRepositoryInterface
{
    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Catalog\Model\Resource\Category
     */
    protected $categoryResource;

    /**
     * List of fields that can used config values in case when value does not defined directly
     *
     * @var array
     */
    protected $useConfigFields = ['available_sort_by', 'default_sort_by', 'filter_price_range'];

    /**
     * @var \Magento\Catalog\Api\Data\CategoryDataBuilder
     */
    protected $categoryBuilder;

    /**
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\Resource\Category $categoryResource
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\Data\CategoryDataBuilder $dataBuilder
     */
    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\Resource\Category $categoryResource,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\Data\CategoryDataBuilder $dataBuilder
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryResource = $categoryResource;
        $this->storeManager = $storeManager;
        $this->categoryBuilder = $dataBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Catalog\Api\Data\CategoryInterface $category)
    {
        $existingData = $category->toFlatArray();
        if ($category->getId()) {
            $existingCategory = $this->get($category->getId());
            if (isset($existingData['image']) && is_array($existingData['image'])) {
                $existingData['image_additional_data'] = $existingData['image'];
                unset($existingData['image']);
            }
            $existingData['id'] = $existingCategory->getId();
            $existingData['parent_id'] = $existingCategory->getParentId();
            $existingData['path'] = $existingCategory->getPath();
            $existingData['is_active'] = $existingCategory->getIsActive();
            $existingData['include_in_menu'] =
                isset($existingData['include_in_menu']) ? (bool)$existingData['include_in_menu'] : false;
            $category->setData($existingData);
        } else {
            $parentId = $category->getParentId() ?: $this->storeManager->getStore()->getRootCategoryId();
            $parentCategory = $this->get($parentId);
            /** @var  $category Category */
            $category->setData($existingData);
            $category->setPath($parentCategory->getPath());
        }
        try {
            $this->validateCategory($category);
            $this->categoryResource->save($category);
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Could not save category: %message', ['message' => $e->getMessage()], $e);
        }
        return $category;
    }

    /**
     * {@inheritdoc}
     */
    public function get($categoryId)
    {
        /** @var Category $category */
        $category = $this->categoryFactory->create();
        $category->load($categoryId);
        if (!$category->getId()) {
            throw NoSuchEntityException::singleField('id', $categoryId);
        }
        return $category;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\Catalog\Api\Data\CategoryInterface $category)
    {
        try {
            $this->categoryResource->delete($category);
        } catch (\Exception $e) {
            throw new StateException(
                'Cannot delete category with id %category_id',
                [
                    'category_id' => $category->getId()
                ],
                $e
            );
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByIdentifier($categoryId)
    {
        $category = $this->get($categoryId);
        return  $this->delete($category);
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
                    $attribute = $this->categoryResource->getAttribute($code)->getFrontend()->getLabel();
                    throw new \Magento\Framework\Model\Exception(__('Attribute "%1" is required.', $attribute));
                } else {
                    throw new \Magento\Framework\Model\Exception($error);
                }
            }
        }
        $category->unsetData('use_post_data_config');
    }
}
