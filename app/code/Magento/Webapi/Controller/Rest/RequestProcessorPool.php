<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Controller\Rest;

/**
 *  Request Processor Pool
 */
class RequestProcessorPool implements RequestProcessorInterface
{

    /**
     * @var array
     */
    private $requestProcessors;

    /**
     * Initial dependencies
     *
     * @param array $requestProcessors
     */
    public function __construct($requestProcessors = [])
    {
        $this->requestProcessors = $requestProcessors;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function process(\Magento\Framework\Webapi\Rest\Request $request)
    {
        $processed = false;

        /**
         * @var RequestProcessorInterface $processor
         */
        foreach ($this->requestProcessors as $processor) {
            if (strpos(ltrim($request->getPathInfo(), '/'), $processor->getProcessorPath()) === 0) {
                $processor->process($request);
                $processed = true;
                break;
            }
        }
        if (!$processed) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Specified request cannot be processed.'),
                null,
                400
            );
        }
    }

    /**
     * Get array of rest processors from di.xml
     *
     * @return array
     */
    public function getProcessors()
    {
        return $this->requestProcessors;
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessorPath()
    {
        return null;
    }
}
