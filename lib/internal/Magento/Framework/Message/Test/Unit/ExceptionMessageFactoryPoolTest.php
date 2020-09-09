<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Message\Test\Unit;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ExceptionMessageFactoryInterface;
use Magento\Framework\Message\ExceptionMessageFactoryPool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExceptionMessageFactoryPoolTest extends TestCase
{
    /**
     * @var ExceptionMessageFactoryInterface|MockObject
     */
    private $defaultExceptionMessageFactoryMock;

    /**
     * @var \Magento\Framework\Message\ExceptionMessageFactoryInterface
     */
    private $specificExceptionMessageFactoryMock;

    /**
     * @var ExceptionMessageFactoryInterface[]|MockObject
     */
    private $exceptionMessageFactoryMapMock;

    /**
     * @var ExceptionMessageFactoryPool
     */
    private $exceptionMessageFactoryPool;

    protected function setUp(): void
    {
        $this->specificExceptionMessageFactoryMock = $this->getMockForAbstractClass(ExceptionMessageFactoryInterface::class);
        $this->defaultExceptionMessageFactoryMock = $this->getMockForAbstractClass(ExceptionMessageFactoryInterface::class);

        $this->exceptionMessageFactoryMapMock = [
            LocalizedException::class => $this->specificExceptionMessageFactoryMock
        ];
        $this->exceptionMessageFactoryPool = new ExceptionMessageFactoryPool(
            $this->defaultExceptionMessageFactoryMock,
            $this->exceptionMessageFactoryMapMock
        );
    }

    public function testSuccessfulDefaultCreateMessage()
    {
        $exception = new \Exception('message');
        $this->assertEquals(
            $this->defaultExceptionMessageFactoryMock,
            $this->exceptionMessageFactoryPool->getMessageFactory($exception)
        );
    }

    public function testSuccessfulSpecificCreateMessage()
    {
        $localizedException = new LocalizedException(__('message'));
        $this->assertEquals(
            $this->specificExceptionMessageFactoryMock,
            $this->exceptionMessageFactoryPool->getMessageFactory($localizedException)
        );
    }
}
