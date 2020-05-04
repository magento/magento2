<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Model\DeploymentConfig;

use Magento\Deploy\Model\DeploymentConfig\ImporterFactory;
use Magento\Framework\App\DeploymentConfig\ImporterInterface;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ImporterFactoryTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var ImporterFactory|MockObject
     */
    private $importerFactory;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $this->importerFactory = new ImporterFactory($this->objectManagerMock);
    }

    public function testCreate()
    {
        $className = 'some/class/name';

        /** @var ImporterInterface|MockObject $importerMock */
        $importerMock = $this->getMockBuilder(ImporterInterface::class)
            ->getMockForAbstractClass();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($className, [])
            ->willReturn($importerMock);

        $this->assertSame($importerMock, $this->importerFactory->create($className));
    }

    /**
     * @codingStandardsIgnoreStart
     * @codingStandardsIgnoreEnd
     */
    public function testCreateWithInvalidArgumentException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage(
            'Type "some/class/name" is not instance of Magento\Framework\App\DeploymentConfig\ImporterInterface'
        );
        $className = 'some/class/name';

        /** @var \StdClass|MockObject $importerMock */
        $importerMock = $this->getMockBuilder(\stdClass::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($className, [])
            ->willReturn($importerMock);

        $this->importerFactory->create($className);
    }
}
