<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CmsUrlRewrite\Test\Unit\Plugin\Cms\Model\ResourceModel;

use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator;

class PageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CmsUrlRewrite\Plugin\Cms\Model\ResourceModel\Page
     */
    protected $pageObject;

    /**
     * @var \Magento\UrlRewrite\Model\UrlPersistInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlPersistMock;

    /**
     * @var \Magento\Cms\Model\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cmsPageMock;

    /**
     * @var \Magento\Cms\Model\ResourceModel\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cmsPageResourceMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->closureMock = function () {
            return 'URL Rewrite Result';
        };

        $this->urlPersistMock = $this->getMockBuilder('Magento\UrlRewrite\Model\UrlPersistInterface')
            ->getMockForAbstractClass();

        $this->cmsPageMock = $this->getMockBuilder('Magento\Cms\Model\Page')
            ->disableOriginalConstructor()
            ->getMock();

        $this->cmsPageResourceMock = $this->getMockBuilder('Magento\Cms\Model\ResourceModel\Page')
            ->disableOriginalConstructor()
            ->getMock();

        $this->pageObject = $objectManager->getObject(
            'Magento\CmsUrlRewrite\Plugin\Cms\Model\ResourceModel\Page',
            [
                'urlPersist' => $this->urlPersistMock
            ]
        );
    }

    public function testAroundDeletePositive()
    {
        $productId = 100;

        $this->cmsPageMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);

        $this->cmsPageMock->expects($this->once())
            ->method('isDeleted')
            ->willReturn(true);

        $this->urlPersistMock->expects($this->once())
            ->method('deleteByData')
            ->with(
                [
                    UrlRewrite::ENTITY_ID => $productId,
                    UrlRewrite::ENTITY_TYPE => CmsPageUrlRewriteGenerator::ENTITY_TYPE
                ]
            );

        $this->assertEquals(
            'URL Rewrite Result',
            $this->pageObject->aroundDelete(
                $this->cmsPageResourceMock,
                $this->closureMock,
                $this->cmsPageMock
            )
        );
    }

    public function testAroundDeleteNegative()
    {
        $this->cmsPageMock->expects($this->once())
            ->method('isDeleted')
            ->willReturn(false);

        $this->urlPersistMock->expects($this->never())
            ->method('deleteByData');

        $this->assertEquals(
            'URL Rewrite Result',
            $this->pageObject->aroundDelete(
                $this->cmsPageResourceMock,
                $this->closureMock,
                $this->cmsPageMock
            )
        );
    }
}
