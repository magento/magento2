<?php
/************************************************************************
 *
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\Plugin;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\HttpInterface as HttpResponse;
use Magento\Customer\Test\Fixture\Customer;
use Magento\GraphQl\Controller\GraphQl as GraphQlController;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GraphQlTest extends TestCase
{
    #[
        DataFixture(Customer::class, as: 'customer'),
    ]
    public function testMutation(): void
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');

        $response = $this->dispatch(
            [
                'query' => sprintf(
                    'mutation {generateCustomerToken(email:"%s",password:"%s"){token}}',
                    $customer->getEmail(),
                    $customer->getPassword()
                )
            ]
        );

        $this->assertEquals('no-cache', $response->getHeader('pragma'));
        $this->assertEquals('no-store, no-cache, must-revalidate, max-age=0', $response->getHeader('cache-control'));
    }

    private function dispatch(array $params): HttpResponse
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var HttpRequest $request */
        $request = $objectManager->create(HttpRequest::class);
        $request->setPathInfo('/graphql');
        $request->setMethod('POST');
        $request->setParams($params);

        // required for \Magento\Framework\App\PageCache\Identifier to generate the correct cache key
        $request->setUri(implode('?', [$request->getPathInfo(), http_build_query($params)]));

        return $objectManager->get(GraphQlController::class)->dispatch($request);
    }
}
