<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CmsUrlRewrite\Test\Unit\Model;

/**
 * Test for \Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator class.
 */
class CmsPageUrlRewriteGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlRewriteFactory;

    /**
     * @var \Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlPathGenerator;

    /**
     * @var \Magento\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator
     */
    private $urlRewriteGenerator;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->urlRewriteFactory = $this->getMockBuilder(\Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlPathGenerator = $this->getMockBuilder(\Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlRewriteGenerator = $this->objectManager->getObject(
            \Magento\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator::class,
            [
                'storeManager' => $this->storeManager,
                'urlRewriteFactory' => $this->urlRewriteFactory,
                'cmsPageUrlPathGenerator' => $this->urlPathGenerator,
            ]
        );
    }

    public function testGenerateForAllStores()
    {
        $initializesStores = [0];
        $cmsPageId = 1;
        /** @var \Magento\Cms\Model\Page|\PHPUnit_Framework_MockObject_MockObject $cmsPage */
        $cmsPage = $this->getMockBuilder(\Magento\Cms\Model\Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cmsPage->expects($this->any())->method('getStores')->willReturn($initializesStores);
        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->setMethods(['getStoreId'])
            ->getMockForAbstractClass();
        $this->storeManager->expects($this->any())->method('getStores')->willReturn([$store]);
        $store->expects($this->any())->method('getStoreId')->willReturn($initializesStores[0]);
        $urlRewrite = $this->getMockBuilder(\Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class)
            ->getMockForAbstractClass();
        $this->urlRewriteFactory->expects($this->any())->method('create')->willReturn($urlRewrite);
        $cmsPage->expects($this->any())->method('getId')->willReturn($cmsPageId);
        $cmsPage->expects($this->any())->method('getIdentifier')->willReturn('request_path');
        $this->urlPathGenerator->expects($this->any())
            ->method('getCanonicalUrlPath')
            ->with($cmsPage)
            ->willReturn('cms/page/view/page_id/' . $cmsPageId);

        $urls = $this->urlRewriteGenerator->generate($cmsPage);
        $this->assertEquals($initializesStores[0], $urls[0]->getStoreId());
        $this->assertFalse(isset($urls[1]));
    }

    public function testGenerateForSpecificStores()
    {
        $initializesStores = [1, 2];
        $cmsPageId = 1;
        /** @var \Magento\Cms\Model\Page|\PHPUnit_Framework_MockObject_MockObject $cmsPage */
        $cmsPage = $this->getMockBuilder(\Magento\Cms\Model\Page::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cmsPage->expects($this->any())->method('getStores')->willReturn($initializesStores);
        $firstStore = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->setMethods(['getStoreId'])
            ->getMockForAbstractClass();
        $secondStore = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->setMethods(['getStoreId'])
            ->getMockForAbstractClass();
        $this->storeManager->expects($this->any())
            ->method('getStores')
            ->willReturn(
                [
                    1 => $firstStore,
                    2 => $secondStore,
                ]
            );
        $firstStore->expects($this->any())->method('getStoreId')->willReturn($initializesStores[0]);
        $secondStore->expects($this->any())->method('getStoreId')->willReturn($initializesStores[1]);

        $urlRewriteFirst = $this->getMockBuilder(\Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class)
            ->getMockForAbstractClass();
        $urlRewriteSecond = $this->getMockBuilder(\Magento\UrlRewrite\Service\V1\Data\UrlRewrite::class)
            ->getMockForAbstractClass();
        $this->urlRewriteFactory->expects($this->at(0))->method('create')->willReturn($urlRewriteFirst);
        $this->urlRewriteFactory->expects($this->at(1))->method('create')->willReturn($urlRewriteSecond);

        $cmsPage->expects($this->any())->method('getId')->willReturn($cmsPageId);
        $cmsPage->expects($this->any())->method('getIdentifier')->willReturn('request_path');
        $this->urlPathGenerator->expects($this->any())->method('getCanonicalUrlPath')->with($cmsPage)
            ->willReturn('cms/page/view/page_id/' . $cmsPageId);
        $urls = $this->urlRewriteGenerator->generate($cmsPage);
        $this->assertEquals(
            [
                $initializesStores[0],
                $initializesStores[1],
            ],
            [
                $urls[0]->getStoreId(),
                $urls[1]->getStoreId(),
            ]
        );
    }
}
