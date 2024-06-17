<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Fixture;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\DataFixtureInterface;

/**
 * Assigning products to catalog
 */
class AssignProducts implements DataFixtureInterface
{
    private const PRODUCTS = 'products';
    private const CATEGORY = 'category';

    /**
     * @var CategoryLinkManagementInterface
     */
    private categoryLinkManagementInterface $categoryLinkManagement;

    /**
     * @param CategoryLinkManagementInterface $categoryLinkManagement
     */
    public function __construct(CategoryLinkManagementInterface $categoryLinkManagement)
    {
        $this->categoryLinkManagement = $categoryLinkManagement;
    }

    /**
     * @inheritdoc
     * @throws InvalidArgumentException
     */
    public function apply(array $data = []): ?DataObject
    {
        if (empty($data[self::CATEGORY])) {
            throw new InvalidArgumentException(__('"%field" is required', ['field' => self::CATEGORY]));
        }

        if (empty($data[self::PRODUCTS])) {
            throw new InvalidArgumentException(__('"%field" is required', ['field' => self::PRODUCTS]));
        }

        if (!is_array($data[self::PRODUCTS])) {
            throw new InvalidArgumentException(__('"%field" must be an array', ['field' => self::PRODUCTS]));
        }

        foreach ($data[self::PRODUCTS] as $product) {
            $this->categoryLinkManagement->assignProductToCategories(
                $product->getSku(),
                [$data[self::CATEGORY]->getId()]
            );
        }

        return null;
    }
}
