<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Category;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
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
     * Authorize saving of a category.
     *
     * @throws AuthorizationException
     * @throws NoSuchEntityException When a category with invalid ID given.
     * @param CategoryInterface|Category $category
     * @return void
     */
    public function authorizeSavingOf(CategoryInterface $category): void
    {
        if (!$this->authorization->isAllowed('Magento_Catalog::edit_category_design')) {
            $notAllowed = false;
            $designAttributeCodes = array_map(
                function (AttributeInterface $attribute) {
                    return $attribute->getAttributeCode();
                },
                $category->getDesignAttributes()
            );
            if (!$category->getId()) {
                foreach ($designAttributeCodes as $attribute) {
                    if ($category->getData($attribute)) {
                        $notAllowed = true;
                        break;
                    }
                }
            } else {
                /** @var Category $savedCategory */
                $savedCategory = $this->categoryFactory->create();
                $savedCategory->load($category->getId());
                if (!$savedCategory->getName()) {
                    throw NoSuchEntityException::singleField('id', $category->getId());
                }
                foreach ($designAttributeCodes as $attribute) {
                    if ($category->getData($attribute) != $savedCategory->getData($attribute)) {
                        $notAllowed = true;
                        break;
                    }
                }
            }

            if ($notAllowed) {
                throw new AuthorizationException(__('Not allowed to edit the category\'s design attributes'));
            }
        }
    }
}
