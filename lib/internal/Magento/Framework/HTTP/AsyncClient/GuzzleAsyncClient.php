<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\HTTP\AsyncClient;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Magento\Framework\HTTP\AsyncClientInterface;

/**
 * Client based on Guzzle HTTP client.
 */
class GuzzleAsyncClient implements AsyncClientInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function request(Request $request): HttpResponseDeferredInterface
    {
        $options = [];
        $options[RequestOptions::HEADERS] = $request->getHeaders();
        if ($request->getBody() !== null) {
            $options[RequestOptions::BODY] = $request->getBody();
        }

        return new GuzzleWrapDeferred(
            $this->client->requestAsync(
                $request->getMethod(),
                $request->getUrl(),
                $options
            )
        );
    }
}
