<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Plugin;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category\Authorization;
use Magento\Framework\Exception\LocalizedException;

/**
 * Perform additional authorization for category operations.
 */
class CategoryAuthorization
{
    /**
     * @var Authorization
     */
    private $authorization;

    /**
     * @param Authorization $authorization
     */
    public function __construct(Authorization $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * Authorize saving of a category.
     *
     * @param CategoryRepositoryInterface $subject
     * @param CategoryInterface $category
     * @throws LocalizedException
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(CategoryRepositoryInterface $subject, CategoryInterface $category): array
    {
        $this->authorization->authorizeSavingOf($category);

        return [$category];
    }
}
