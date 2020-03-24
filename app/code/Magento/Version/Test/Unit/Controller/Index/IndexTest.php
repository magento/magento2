<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Version\Test\Unit\Controller\Index;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Version\Controller\Index\Index as VersionIndex;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    /**
     * @var VersionIndex
     */
    private $versionController;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadataMock;

    /**
     * @var ResponseInterface
     */
    private $responseMock;

    /**
     * Prepare test preconditions
     */
    protected function setUp()
    {
        $this->productMetadataMock = $this->getMockBuilder(ProductMetadataInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName', 'getEdition', 'getVersion'])
            ->getMock();

        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setBody', 'sendResponse'])
            ->getMock();

        $this->versionController = new VersionIndex($this->responseMock, $this->productMetadataMock);
    }

    /**
     * Git Base version does not return information about version
     */
    public function testGitBasedInstallationDoesNotReturnVersion(): void
    {
        $this->productMetadataMock->expects($this->any())
            ->method('getVersion')
            ->willReturn('dev-2.3');

        $this->responseMock->expects($this->never())
            ->method('setBody');

        $this->assertNull($this->versionController->execute());
    }

    /**
     * Magento Community returns information about major and minor version of product
     */
    public function testCommunityVersionDisplaysMajorMinorVersionAndEditionName(): void
    {
        $this->productMetadataMock->expects($this->any())->method('getVersion')->willReturn('2.3.3');
        $this->productMetadataMock->expects($this->any())->method('getEdition')->willReturn('Community');
        $this->productMetadataMock->expects($this->any())->method('getName')->willReturn('Magento');

        $this->responseMock->expects($this->once())->method('setBody')
            ->with('Magento/2.3 (Community)')
            ->will($this->returnSelf());

        $this->versionController->execute();
    }
}
