<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Placeholder;

use Magento\Config\Model\Placeholder\Environment;
use Magento\Config\Model\Placeholder\PlaceholderFactory;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PlaceholderFactoryTest extends TestCase
{
    /**
     * @var PlaceholderFactory
     */
    private $model;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var Environment|MockObject
     */
    private $environmentMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $this->environmentMock = $this->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new PlaceholderFactory(
            $this->objectManagerMock,
            [
                PlaceholderFactory::TYPE_ENVIRONMENT => Environment::class,
                'wrongClass' => \stdClass::class,
            ]
        );
    }

    public function testCreate()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(Environment::class)
            ->willReturn($this->environmentMock);

        $this->assertInstanceOf(
            Environment::class,
            $this->model->create(PlaceholderFactory::TYPE_ENVIRONMENT)
        );
    }

    public function testCreateNonExisted()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('There is no defined type dummyClass');
        $this->model->create('dummyClass');
    }

    public function testCreateWrongImplementation()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage(
            'Object is not instance of Magento\Config\Model\Placeholder\PlaceholderInterface'
        );
        $this->model->create('wrongClass');
    }
}
