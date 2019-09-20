<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Category;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Additional authorization for category operations.
 */
class Authorization
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @param AuthorizationInterface $authorization
     * @param CategoryFactory $factory
     */
    public function __construct(AuthorizationInterface $authorization, CategoryFactory $factory)
    {
        $this->authorization = $authorization;
        $this->categoryFactory = $factory;
    }

    /**
     * Determine whether a category has design properties changed.
     *
     * @param CategoryModel $category
     * @param CategoryModel|null $oldCategory
     * @return bool
     */
    private function hasChanges(CategoryModel $category, ?CategoryModel $oldCategory): bool
    {
        foreach ($category->getDesignAttributes() as $designAttribute) {
            $oldValues = [null];
            if ($oldCategory) {
                //New value must match saved value exactly
                $oldValues = [$oldCategory->getData($designAttribute->getAttributeCode())];
                if (empty($oldValues[0])) {
                    $oldValues[0] = null;
                }
            } else {
                //New value can be either empty or default value.
                $oldValues[] = $designAttribute->getDefaultValue();
            }
            $newValue = $category->getData($designAttribute->getAttributeCode());
            if (empty($newValue)) {
                $newValue = null;
            }

            if (!in_array($newValue, $oldValues, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Authorize saving of a category.
     *
     * @throws AuthorizationException
     * @throws NoSuchEntityException When a category with invalid ID given.
     * @param CategoryInterface|CategoryModel $category
     * @return void
     */
    public function authorizeSavingOf(CategoryInterface $category): void
    {
        if (!$this->authorization->isAllowed('Magento_Catalog::edit_category_design')) {
            $savedCategory = null;
            if ($category->getId()) {
                /** @var CategoryModel $savedCategory */
                $savedCategory = $this->categoryFactory->create();
                $savedCategory->load($category->getId());
                if (!$savedCategory->getName()) {
                    throw NoSuchEntityException::singleField('id', $category->getId());
                }
            }

            if ($this->hasChanges($category, $savedCategory)) {
                throw new AuthorizationException(__('Not allowed to edit the category\'s design attributes'));
            }
        }
    }
}
