<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Resolver;

use Magento\Eav\Model\AttributeRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\InputException;

/**
 * Return attributes filtered and errors if there is some filter that cannot be applied
 */
class GetFilteredAttributes
{
    /**
     * @var AttributeRepository
     */
    private AttributeRepository $attributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var EntityFieldChecker
     */
    private EntityFieldChecker $entityFieldChecker;

    /**
     * @param AttributeRepository       $attributeRepository
     * @param SearchCriteriaBuilder     $searchCriteriaBuilder
     * @param EntityFieldChecker        $entityFieldChecker
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        EntityFieldChecker $entityFieldChecker
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->entityFieldChecker = $entityFieldChecker;
    }

    /**
     * Return the attributes filtered and errors if the filter could not be applied
     *
     * @param array $filterArgs
     * @param string $entityType
     * @return array
     * @throws InputException
     */
    public function execute(array $filterArgs, string $entityType): array
    {
        $errors = [];
        foreach ($filterArgs as $field => $value) {
            if ($this->entityFieldChecker->fieldBelongToEntity(strtolower($entityType), $field)) {
                $this->searchCriteriaBuilder->addFilter($field, $value);
            } else {
                $errors[] = [
                    'type' => 'FILTER_NOT_FOUND',
                    'message' =>
                        (string)__(
                            'Cannot filter by "%filter" as that field does not belong to "%entity".',
                            ['filter' => $field, 'entity' => $entityType]
                        )
                ];
            }
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_visible', true)
            ->addFilter('backend_type', 'static', 'neq')
            ->create();

        $attributesList = $this->attributeRepository->getList(strtolower($entityType), $searchCriteria)->getItems();

        return [
            'items' => $attributesList,
            'errors' => $errors
        ];
    }
}
