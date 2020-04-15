<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model\DeploymentConfig;

use Magento\Deploy\Model\DeploymentConfig\ImporterFactory;
use Magento\Framework\App\DeploymentConfig\ImporterInterface;
use Magento\Framework\ObjectManagerInterface;

class ImporterFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $objectManagerMock;

    /**
     * @var ImporterFactory|\PHPUnit\Framework\MockObject\MockObject
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

        /** @var ImporterInterface|\PHPUnit\Framework\MockObject\MockObject $importerMock */
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
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Type "some/class/name" is not instance of Magento\\Framework\\App\\DeploymentConfig\\ImporterInterface');

        $className = 'some/class/name';

        /** @var \StdClass|\PHPUnit\Framework\MockObject\MockObject $importerMock */
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
