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

    /** @var array  */
    private $requestProcessors;

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    private $objectManager;


    /**
     * RequestProcessorPool constructor.
     * @param array $requestProcessors
     */
    public function __construct($requestProcessors = [])
    {
        $this->requestProcessors = $requestProcessors;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function process(\Magento\Framework\Webapi\Rest\Request $request)
    {
        $processed = false;
        foreach ($this->requestProcessors as $path => $name) {
            if (strpos(ltrim($request->getPathInfo(), '/'), $path) === 0) {
                /**@var RequestProcessorInterface $processor */
                $processor = $this->objectManager->get($name);
                $processor->process($request);
                $processed = true;
                break;
            }
        }
        if (!$processed) {
            throw new \Magento\Framework\Exception\NotFoundException(__('Specified request cannot be processed.'));
        }
    }

}