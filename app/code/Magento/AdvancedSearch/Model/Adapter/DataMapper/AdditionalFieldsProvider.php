<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Model\Adapter\DataMapper;

/**
 * Provide additional fields for data mapper during search indexer
 * Must return array with the following format: [[product id] => [field name1 => value1, ...], ...]
 */
class AdditionalFieldsProvider implements AdditionalFieldsProviderInterface
{
    /**
     * @var AdditionalFieldsProviderInterface[]
     */
    private $fieldsProviders;

    /**
     * @param AdditionalFieldsProviderInterface[] $fieldsProviders
     */
    public function __construct(array $fieldsProviders)
    {
        $this->fieldsProviders = $fieldsProviders;
    }

    /**
     * {@inheritdoc}
     */
    public function getFields(array $productIds, $storeId)
    {
        $fields = [];
        foreach ($this->fieldsProviders as $fieldsProvider) {
            $fields[] = $fieldsProvider->getFields($productIds, $storeId);
        }

        return array_replace_recursive(...$fields);
    }
}
