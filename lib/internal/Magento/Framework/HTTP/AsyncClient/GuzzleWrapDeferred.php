<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\HTTP\AsyncClient;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\CancellationException;
use GuzzleHttp\Promise\PromiseInterface;
use Magento\Framework\Async\CancelingDeferredException;
use Psr\Http\Message\ResponseInterface;

/**
 * Wrapper around guzzle's response promise.
 */
class GuzzleWrapDeferred implements HttpResponseDeferredInterface
{
    /**
     * @var PromiseInterface
     */
    private $promise;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var HttpException
     */
    private $exception;

    /**
     * @var bool
     */
    private $canceled = false;

    /**
     * @param PromiseInterface $promise
     */
    public function __construct(PromiseInterface $promise)
    {
        $this->promise = $promise;
    }

    /**
     * @inheritDoc
     */
    public function isDone(): bool
    {
        return $this->response || $this->exception;
    }

    /**
     * Convert guzzle response to Magento response.
     *
     * @param ResponseInterface $response
     * @return Response
     */
    private function convertResponse(ResponseInterface $response): Response
    {
        /** @var string[] $headers */
        $headers = [];
        foreach ($response->getHeaders() as $name => $values) {
            $headers[$name] = implode(', ', $values);
        }
        return new Response($response->getStatusCode(), $headers, $response->getBody()->getContents());
    }

    /**
     * Unwrap guzzle's promise.
     */
    private function unwrap(): void
    {
        try {
            /** @var ResponseInterface $response */
            $response = $this->promise->wait();
            $this->response = $this->convertResponse($response);
        } catch (RequestException $requestException) {
            if ($requestException instanceof BadResponseException) {
                $this->response = $this->convertResponse($requestException->getResponse());
            } else {
                $this->exception = new HttpException($requestException->getMessage(), 0, $requestException);
            }
        } catch (\Throwable $exception) {
            $this->exception = $exception;
        }
    }

    /**
     * @inheritDoc
     */
    public function get(): Response
    {
        if ($this->isCancelled()) {
            throw new CancelingDeferredException('Deferred is canceled');
        }
        if (!$this->isDone()) {
            $this->unwrap();
        }

        if ($this->exception) {
            throw $this->exception;
        }
        return $this->response;
    }

    /**
     * @inheritDoc
     */
    public function cancel(bool $force = false): void
    {
        if ($force) {
            $this->promise->cancel();
            if ($this->promise->getState() === PromiseInterface::REJECTED) {
                $this->unwrap();
                if ($this->exception instanceof CancellationException) {
                    $this->canceled = true;
                    return;
                }
            }
        }

        throw new CancelingDeferredException('Failed to cancel HTTP request');
    }

    /**
     * @inheritDoc
     */
    public function isCancelled(): bool
    {
        return $this->canceled;
    }
}
