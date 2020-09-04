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
use Magento\Catalog\Model\Category\Attribute\Backend\LayoutUpdate;

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
     * Extract attribute value from the model.
     *
     * @param CategoryModel $category
     * @param AttributeInterface $attr
     * @throws \RuntimeException When no new value is present.
     * @return mixed
     */
    private function extractAttributeValue(CategoryModel $category, AttributeInterface $attr)
    {
        if ($category->hasData($attr->getAttributeCode())) {
            $newValue = $category->getData($attr->getAttributeCode());
        } elseif ($category->hasData(CategoryModel::CUSTOM_ATTRIBUTES)
            && $attrValue = $category->getCustomAttribute($attr->getAttributeCode())
        ) {
            $newValue = $attrValue->getValue();
        } else {
            throw new \RuntimeException('New value is not set');
        }

        if (empty($newValue)
            || ($attr->getBackend() instanceof LayoutUpdate
                && ($newValue === LayoutUpdate::VALUE_USE_UPDATE_XML || $newValue === LayoutUpdate::VALUE_NO_UPDATE)
            )
        ) {
            $newValue = null;
        }

        return $newValue;
    }

    /**
     * Find values to compare the new one.
     *
     * @param AttributeInterface $attribute
     * @param array|null $oldCategory
     * @return mixed[]
     */
    private function fetchOldValue(AttributeInterface $attribute, ?array $oldCategory): array
    {
        $oldValues = [null];
        $attrCode = $attribute->getAttributeCode();
        if ($oldCategory) {
            //New value must match saved value exactly
            $oldValues = [!empty($oldCategory[$attrCode]) ? $oldCategory[$attrCode] : null];
            if (empty($oldValues[0])) {
                $oldValues[0] = null;
            }
        } else {
            //New value can be either empty or default value.
            $oldValues[] = $attribute->getDefaultValue();
        }

        return $oldValues;
    }

    /**
     * Determine whether a category has design properties changed.
     *
     * @param CategoryModel $category
     * @param array|null $oldCategory
     * @return bool
     */
    private function hasChanges(CategoryModel $category, ?array $oldCategory): bool
    {
        foreach ($category->getDesignAttributes() as $designAttribute) {
            $oldValues = $this->fetchOldValue($designAttribute, $oldCategory);
            try {
                $newValue = $this->extractAttributeValue($category, $designAttribute);
            } catch (\RuntimeException $exception) {
                //No new value
                continue;
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
            $oldData = null;
            if ($category->getId()) {
                if ($category->getOrigData()) {
                    $oldData = $category->getOrigData();
                } else {
                    /** @var CategoryModel $savedCategory */
                    $savedCategory = $this->categoryFactory->create();
                    $savedCategory->load($category->getId());
                    if (!$savedCategory->getName()) {
                        throw NoSuchEntityException::singleField('id', $category->getId());
                    }
                    $oldData = $savedCategory->getData();
                }
            }

            if ($this->hasChanges($category, $oldData)) {
                throw new AuthorizationException(__('Not allowed to edit the category\'s design attributes'));
            }
        }
    }
}
