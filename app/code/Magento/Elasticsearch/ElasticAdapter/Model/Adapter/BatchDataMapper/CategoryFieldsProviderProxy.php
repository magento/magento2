<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\ElasticAdapter\Model\Adapter\BatchDataMapper;

use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;
use Magento\AdvancedSearch\Model\Client\ClientResolver;

/**
 * Proxy for data mapping of categories fields
 * @deprecated Elasticsearch is no longer supported by Adobe
 * @see this class will be responsible for ES only
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
     * Get Category Fields Provider
     *
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
