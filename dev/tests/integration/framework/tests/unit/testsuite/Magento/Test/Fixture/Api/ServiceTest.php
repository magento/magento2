<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Fixture\Api;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\TestFramework\Fixture\Api\Service;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Test fixture api service
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ServiceTest extends TestCase
{
    /**
     * @var Service
     */
    private $model;

    /**
     * @var MockObject|stdClass
     */
    private $fakeClass;

    /**
     * @ingeritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $serviceInputProcessor = $this->createMock(ServiceInputProcessor::class);
        $serviceInputProcessor->expects($this->once())
            ->method('process')
            ->willReturnCallback(
                function (string $serviceClassName, string $serviceMethodName, array $params) {
                    return array_values($params);
                }
            );

        $this->fakeClass = $this->getMockBuilder(stdClass::class)
            ->addMethods(['fakeMethod'])
            ->getMock();

        $objectManager->expects($this->once())
            ->method('get')
            ->willReturn($this->fakeClass);

        $this->model = new Service(
            $objectManager,
            $serviceInputProcessor,
            get_class($this->fakeClass),
            'fakeMethod'
        );
    }

    /**
     * Test that the service method is executed with correct parameters
     */
    public function testExecute(): void
    {
        $params = ['param1' => 'test1', 'param2' => 'test2'];
        $this->fakeClass->expects($this->once())
            ->method('fakeMethod')
            ->with('test1', 'test2');

        $this->model->execute($params);
    }
}
