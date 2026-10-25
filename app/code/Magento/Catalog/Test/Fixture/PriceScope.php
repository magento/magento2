<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Fixture;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

/**
 * Fixture to change price attribute scope
 *
 * Usage examples:
 *
 * 1. Change price scope to website
 * <pre>
 *  #[
 *      DataFixture(PriceScope::class, ['scope' => ProductAttributeInterface::SCOPE_WEBSITE_TEXT])
 *  ]
 * </pre>
 */
class PriceScope implements RevertibleDataFixtureInterface
{
    /**
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        private readonly ProductAttributeRepositoryInterface $productAttributeRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    /**
     * @inheritDoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $this->change($data['scope']);
        return null;
    }

    /**
     * @inheritDoc
     */
    public function revert(DataObject $data): void
    {
        $this->change(ProductAttributeInterface::SCOPE_GLOBAL_TEXT);
    }

    /**
     * Change price attributes scope
     *
     * @param string $scope
     * @return void
     */
    private function change(string $scope): void
    {
        $this->searchCriteriaBuilder->addFilter('frontend_input', 'price');
        $criteria = $this->searchCriteriaBuilder->create();
        foreach ($this->productAttributeRepository->getList($criteria)->getItems() as $priceAttribute) {
            $priceAttribute->setScope($scope);
            $this->productAttributeRepository->save($priceAttribute);
        }
    }
}
