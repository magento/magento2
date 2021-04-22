<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Fixture;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\ServiceInputProcessor;

/**
 * Abstract class for api data fixtures
 */
abstract class AbstractApiDataFixture implements RevertibleDataFixtureInterface, ApiDataFixtureInterface
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
     * @param ObjectManagerInterface $objectManager
     * @param ServiceInputProcessor $serviceInputProcessor
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ServiceInputProcessor $serviceInputProcessor
    ) {
        $this->objectManager = $objectManager;
        $this->serviceInputProcessor = $serviceInputProcessor;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?array
    {
        $data = $this->processServiceMethodParameters($data);
        $service = $this->getService();
        $serviceInstance = $this->objectManager->get($service[ApiDataFixtureInterface::SERVICE_CLASS]);
        $params = $this->serviceInputProcessor->process(
            $service[ApiDataFixtureInterface::SERVICE_CLASS],
            $service[ApiDataFixtureInterface::SERVICE_METHOD],
            $data
        );
        $result = call_user_func_array([$serviceInstance, $service[ApiDataFixtureInterface::SERVICE_METHOD]], $params);

        return $this->processServiceResult($data, $result);
    }

    /**
     * @inheritdoc
     */
    public function revert(array $data = []): void
    {
        $data = $this->processRollbackServiceMethodParameters($data);
        $service = $this->getRollbackService();
        $serviceInstance = $this->objectManager->get($service[ApiDataFixtureInterface::SERVICE_CLASS]);
        $params = $this->serviceInputProcessor->process(
            $service[ApiDataFixtureInterface::SERVICE_CLASS],
            $service[ApiDataFixtureInterface::SERVICE_METHOD],
            $data
        );
        try {
            call_user_func_array([$serviceInstance, $service[ApiDataFixtureInterface::SERVICE_METHOD]], $params);
        } catch (NoSuchEntityException $exception) {
            //ignore
        }
    }
}
