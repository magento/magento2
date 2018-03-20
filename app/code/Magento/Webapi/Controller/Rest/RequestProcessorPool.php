<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Controller\Rest;

/**
 *  Request Processor Pool
 */
class RequestProcessorPool
{

    /**
     * @var array
     */
    private $requestProcessors;

    /**
     * Initial dependencies
     *
     * @param RequestProcessorInterface[] $requestProcessors
     */
    public function __construct($requestProcessors = [])
    {
        $this->requestProcessors = $requestProcessors;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Magento\Framework\Webapi\Exception
     * return RequestProcessorInterface
     */
    public function getProcessor(\Magento\Framework\Webapi\Rest\Request $request)
    {
        /**
         * @var RequestProcessorInterface $processor
         */
        foreach ($this->requestProcessors as $processor) {
            if ($processor->canProcess($request)) {
                return $processor;
            }
        }

        throw new \Magento\Framework\Webapi\Exception(
            __('Specified request cannot be processed.'),
            0,
            \Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST
        );
    }
}
