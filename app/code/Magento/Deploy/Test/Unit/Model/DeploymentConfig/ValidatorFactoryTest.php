<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Model\DeploymentConfig;

use Magento\Deploy\Model\DeploymentConfig\ValidatorFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Config\Validator;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;

class ValidatorFactoryTest extends TestCase
{
    /**
     * @var ValidatorFactory
     */
    private $model;

    /**
     * @var ObjectManagerInterface|Mock
     */
    private $objectManagerMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();

        $this->model = new ValidatorFactory($this->objectManagerMock);
    }

    public function testCreate()
    {
        $validatorMock = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(Validator::class)
            ->willReturn($validatorMock);

        $this->assertInstanceOf(
            Validator::class,
            $this->model->create(Validator::class)
        );
    }

    /**
     * @codingStandardsIgnoreStart
     * @codingStandardsIgnoreEnd
     */
    public function testCreateWrongImplementation()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage(
            'Type "className" is not instance of Magento\Framework\App\DeploymentConfig\ValidatorInterface'
        );
        $className = 'className';

        $stdMock = $this->getMockBuilder(\stdClass::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($className, [])
            ->willReturn($stdMock);

        $this->model->create($className);
    }
}
