<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Version\Test\Unit\Controller\Index;

use Magento\Version\Controller\Index\Index as VersionIndex;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class \Magento\Version\Test\Unit\Controller\Index\IndexTest
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var VersionIndex
     */
    private $model;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * Prepare test preconditions
     */
    protected function setUp()
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->productMetadata = $this->getMockBuilder(ProductMetadataInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName', 'getEdition', 'getVersion'])
            ->getMock();

        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setBody', 'sendResponse'])
            ->getMock();

        $this->context->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->response);

        $helper = new ObjectManager($this);

        $this->model = $helper->getObject(
            'Magento\Version\Controller\Index\Index',
            [
                'context' => $this->context,
                'productMetadata' => $this->productMetadata
            ]
        );
    }

    /**
     * Test with Git Base version
     */
    public function testExecuteWithGitBase()
    {
        $this->productMetadata->expects($this->any())->method('getVersion')->willReturn('dev-2.3');
        $this->assertNull($this->model->execute());
    }

    /**
     * Test with Community Version
     */
    public function testExecuteWithCommunityVersion()
    {
        $this->productMetadata->expects($this->any())->method('getVersion')->willReturn('2.3.3');
        $this->productMetadata->expects($this->any())->method('getEdition')->willReturn('Community');
        $this->productMetadata->expects($this->any())->method('getName')->willReturn('Magento');
        $this->response->expects($this->once())->method('setBody')
            ->with('Magento/2.3 (Community)')
            ->will($this->returnSelf());
        $this->model->execute();
    }
}
