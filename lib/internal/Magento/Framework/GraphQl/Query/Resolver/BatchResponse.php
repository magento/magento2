<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver;

/**
 * Contains responses for batch requests.
 */
class BatchResponse
{
    /**
     * @var \SplObjectStorage
     */
    private $responses;

    /**
     * BatchResponse constructor.
     */
    public function __construct()
    {
        $this->responses = new \SplObjectStorage();
    }

    /**
     * Match response with request.
     *
     * @param BatchRequestItemInterface $request
     * @param array|int|string|float|Value $response
     * @return void
     */
    public function addResponse(BatchRequestItemInterface $request, $response): void
    {
        $this->responses[$request] = $response;
    }

    /**
     * Get response assigned to the request.
     *
     * @param BatchRequestItemInterface $item
     * @return mixed|Value
     * @throws \InvalidArgumentException
     */
    public function findResponseFor(BatchRequestItemInterface $item)
    {
        if (!$this->responses->offsetExists($item)) {
            throw new \InvalidArgumentException('Response does not exist');
        }

        return $this->responses[$item];
    }
}
