<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Version\Test\Unit\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Version\Controller\Index\Index as VersionIndex;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    /** @var VersionIndex */
    private $versionController;

    /** @var MockObject|ProductMetadataInterface */
    private $productMetadataMock;

    /** @var MockObject|RawFactory */
    private $rawResponseFactoryMock;

    /** @var MockObject|Raw */
    private $rawResponseMock;

    /** @var MockObject|Context */
    private $contextMock;

    /**
     * Prepare test preconditions
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);

        $this->productMetadataMock = $this->getMockBuilder(ProductMetadataInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getName', 'getEdition', 'getVersion'])
            ->getMockForAbstractClass();

        $this->rawResponseFactoryMock = $this->createPartialMock(RawFactory::class, ['create']);
        $this->rawResponseMock = $this->createPartialMock(Raw::class, ['setContents']);
        $this->rawResponseFactoryMock->method('create')->willReturn($this->rawResponseMock);

        $this->versionController = new VersionIndex(
            $this->contextMock,
            $this->rawResponseFactoryMock,
            $this->productMetadataMock
        );
    }

    /**
     * Git Base version does not return information about version
     */
    public function testGitBasedInstallationDoesNotReturnVersion(): void
    {
        $this->productMetadataMock->expects($this->any())
            ->method('getVersion')
            ->willReturn('dev-2.3');

        $this->rawResponseMock->expects($this->never())
            ->method('setContents');

        $this->versionController->execute();
    }

    /**
     * Magento Community returns information about major and minor version of product
     */
    public function testCommunityVersionDisplaysMajorMinorVersionAndEditionName(): void
    {
        $this->productMetadataMock->expects($this->any())->method('getVersion')->willReturn('2.3.3');
        $this->productMetadataMock->expects($this->any())->method('getEdition')->willReturn('Community');
        $this->productMetadataMock->expects($this->any())->method('getName')->willReturn('Magento');

        $this->rawResponseMock->expects($this->once())->method('setContents')
            ->with('Magento/2.3 (Community)')
            ->willReturnSelf();

        $this->versionController->execute();
    }
}
