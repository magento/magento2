<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture\Api;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\ServiceInputProcessor;

/**
 * Api service
 */
class Service
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ServiceInputProcessor
     */
    private $serviceInputProcessor;

    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $methodName;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ServiceInputProcessor $serviceInputProcessor
     * @param string $className
     * @param string $methodName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ServiceInputProcessor $serviceInputProcessor,
        string $className,
        string $methodName
    ) {
        $this->objectManager = $objectManager;
        $this->serviceInputProcessor = $serviceInputProcessor;
        $this->className = $className;
        $this->methodName = $methodName;
    }

    /**
     * Execute the Api service with provided the data
     *
     * @param array $data
     * @return mixed
     */
    public function execute(array $data)
    {
        $params = $this->serviceInputProcessor->process(
            $this->className,
            $this->methodName,
            $data
        );
        $service = $this->objectManager->get($this->className);

        return call_user_func_array([$service, $this->methodName], $params);
    }
}
