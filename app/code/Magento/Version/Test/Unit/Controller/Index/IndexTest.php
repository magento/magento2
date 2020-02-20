<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Version\Test\Unit\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Version\Controller\Index\Index as VersionIndex;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    /**
     * @var VersionIndex
     */
    private $model;

    /**
     * @var Context
     */
    private $contextMock;

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
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productMetadataMock = $this->getMockBuilder(ProductMetadataInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName', 'getEdition', 'getVersion'])
            ->getMock();

        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setBody', 'sendResponse'])
            ->getMock();

        $this->contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->responseMock);

        $objectManager = new ObjectManager($this);

        $this->model = $objectManager->getObject(
            VersionIndex::class,
            [
                'context' => $this->contextMock,
                'productMetadata' => $this->productMetadataMock
            ]
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

        $this->responseMock->expects($this->never())
            ->method('setBody');

        $this->assertNull($this->model->execute());
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

        $this->model->execute();
    }
}
