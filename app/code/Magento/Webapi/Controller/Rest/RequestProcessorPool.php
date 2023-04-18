<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Controller\Rest;

use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Request;

/**
 *  Request Processor Pool
 */
class RequestProcessorPool
{
    /**
     * Initial dependencies
     *
     * @param RequestProcessorInterface[] $requestProcessors
     */
    public function __construct(
        private $requestProcessors = []
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     * return RequestProcessorInterface
     */
    public function getProcessor(Request $request)
    {
        foreach ($this->requestProcessors as $processor) {
            if ($processor->canProcess($request)) {
                return $processor;
            }
        }

        throw new Exception(
            __('Specified request cannot be processed.'),
            0,
            Exception::HTTP_BAD_REQUEST
        );
    }
}
