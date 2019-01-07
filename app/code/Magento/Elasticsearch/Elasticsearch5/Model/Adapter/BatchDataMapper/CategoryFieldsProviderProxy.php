<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Elasticsearch5\Model\Adapter\BatchDataMapper;

use Magento\AdvancedSearch\Model\Client\ClientResolver;
use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;

/**
 * Proxy for data mapping of categories fields
 */
class CategoryFieldsProviderProxy implements AdditionalFieldsProviderInterface
{
    /**
     * @var ClientResolver
     */
    private $clientResolver;

    /**
     * @var AdditionalFieldsProviderInterface[]
     */
    private $categoryFieldsProviders;

    /**
     * CategoryFieldsProviderProxy constructor.
     * @param ClientResolver $clientResolver
     * @param AdditionalFieldsProviderInterface[] $categoryFieldsProviders
     */
    public function __construct(
        ClientResolver $clientResolver,
        array $categoryFieldsProviders
    ) {
        $this->clientResolver = $clientResolver;
        $this->categoryFieldsProviders = $categoryFieldsProviders;
    }

    /**
     * @return AdditionalFieldsProviderInterface
     */
    private function getCategoryFieldsProvider()
    {
        return $this->categoryFieldsProviders[$this->clientResolver->getCurrentEngine()];
    }

    /**
     * @inheritdoc
     */
    public function getFields(array $productIds, $storeId)
    {
        return $this->getCategoryFieldsProvider()->getFields($productIds, $storeId);
    }
}
