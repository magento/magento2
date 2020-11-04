<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SearchStorefrontElasticsearch\Elasticsearch5\Model\Client;

use Magento\SearchStorefrontAdvancedSearch\Model\Client\ClientFactoryInterface;
use Magento\SearchStorefrontAdvancedSearch\Model\Client\ClientResolver;

/**
 * Proxy for client factories
 */
class ClientFactoryProxy implements ClientFactoryInterface
{
    /**
     * @var ClientResolver
     */
    private $clientResolver;

    /**
     * @var ClientFactoryInterface[]
     */
    private $clientFactories;

    /**
     * CategoryFieldsProviderProxy constructor.
     * @param ClientResolver $clientResolver
     * @param ClientFactoryInterface[] $clientFactories
     */
    public function __construct(
        ClientResolver $clientResolver,
        array $clientFactories
    ) {
        $this->clientResolver = $clientResolver;
        $this->clientFactories = $clientFactories;
    }

    /**
     * @return ClientFactoryInterface
     */
    private function getClientFactory()
    {
        return $this->clientFactories[$this->clientResolver->getCurrentEngine()];
    }

    /**
     * @inheritdoc
     */
    public function create(array $options = [])
    {
        return $this->getClientFactory()->create($options);
    }
}
