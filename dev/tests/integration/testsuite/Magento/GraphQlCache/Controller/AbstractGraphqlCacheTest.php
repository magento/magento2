<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller;

use PHPUnit\Framework\TestCase;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Abstract test class for Graphql cache tests
 */
abstract class AbstractGraphqlCacheTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Prepare a query and return a request to be used in the same test end to end
     *
     * @param string $query
     * @return \Magento\Framework\App\Request\Http
     */
    protected function prepareRequest(string $query) : \Magento\Framework\App\Request\Http
    {
        $cacheableQuery = $this->objectManager->get(\Magento\GraphQlCache\Model\CacheableQuery::class);
        $cacheableQueryReflection = new \ReflectionProperty(
            $cacheableQuery,
            'cacheTags'
        );
        $cacheableQueryReflection->setAccessible(true);
        $cacheableQueryReflection->setValue($cacheableQuery, []);

        /** @var \Magento\Framework\UrlInterface $urlInterface */
        $urlInterface = $this->objectManager->create(\Magento\Framework\UrlInterface::class);
        //set unique URL
        $urlInterface->setQueryParam('query', $query);

        $request = $this->objectManager->get(\Magento\Framework\App\Request\Http::class);
        $request->setUri($urlInterface->getUrl('graphql'));
        $request->setMethod('GET');
        //set the actual GET query
        $request->setQueryValue('query', $query);
        return $request;
    }
}
