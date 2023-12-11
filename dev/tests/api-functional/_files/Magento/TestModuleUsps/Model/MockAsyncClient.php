<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleUsps\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\AsyncClient\HttpResponseDeferredInterface;
use Magento\Framework\HTTP\AsyncClient\Request;
use Magento\Framework\HTTP\AsyncClient\ResponseFactory;
use Magento\Framework\HTTP\AsyncClientInterface;

/**
 * Mock async client returns USPS rate responses
 */
class MockAsyncClient implements AsyncClientInterface
{
    /**
     * @var MockResponseBodyLoader
     */
    private $mockResponseBodyLoader;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @param MockResponseBodyLoader $mockResponseBodyLoader
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        MockResponseBodyLoader $mockResponseBodyLoader,
        ResponseFactory $responseFactory
    ) {
        $this->mockResponseBodyLoader = $mockResponseBodyLoader;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Fetch mock USPS rate response
     *
     * @param Request $request
     * @return HttpResponseDeferredInterface
     * @throws LocalizedException
     */
    public function request(Request $request): HttpResponseDeferredInterface
    {
        return new MockDeferredResponse(
            $this->responseFactory->create(
                [
                    'statusCode' => 200,
                    'headers' => [],
                    'body' => $this->mockResponseBodyLoader->loadForRequest($request),
                ]
            )
        );
    }
}
