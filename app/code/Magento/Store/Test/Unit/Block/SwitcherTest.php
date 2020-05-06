<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Block;

use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Block\Switcher;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SwitcherTest extends TestCase
{
    /** @var Switcher */
    protected $switcher;

    /** @var Context|MockObject */
    protected $context;

    /** @var PostHelper|MockObject */
    protected $corePostDataHelper;

    /** @var StoreManagerInterface|MockObject */
    protected $storeManager;

    /** @var UrlInterface|MockObject */
    protected $urlBuilder;

    /** @var StoreInterface|MockObject */
    private $store;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();
        $this->urlBuilder = $this->getMockForAbstractClass(UrlInterface::class);
        $this->context = $this->createMock(Context::class);
        $this->context->expects($this->any())->method('getStoreManager')->willReturn($this->storeManager);
        $this->context->expects($this->any())->method('getUrlBuilder')->willReturn($this->urlBuilder);
        $this->corePostDataHelper = $this->createMock(PostHelper::class);
        $this->store = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->switcher = (new ObjectManager($this))->getObject(
            Switcher::class,
            [
                'context' => $this->context,
                'postDataHelper' => $this->corePostDataHelper,
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetTargetStorePostData()
    {
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->any())
            ->method('getCode')
            ->willReturn('new-store');
        $storeSwitchUrl = 'http://domain.com/stores/store/redirect';
        $store->expects($this->atLeastOnce())
            ->method('getCurrentUrl')
            ->with(false)
            ->willReturn($storeSwitchUrl);
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->store);
        $this->store->expects($this->once())
            ->method('getCode')
            ->willReturn('old-store');
        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->willReturn($storeSwitchUrl);
        $this->corePostDataHelper->expects($this->any())
            ->method('getPostData')
            ->with($storeSwitchUrl, ['___store' => 'new-store', 'uenc' => null, '___from_store' => 'old-store']);

        $this->switcher->getTargetStorePostData($store);
    }

    /**
     * @dataProvider isStoreInUrlDataProvider
     * @param bool $isUseStoreInUrl
     */
    public function testIsStoreInUrl($isUseStoreInUrl)
    {
        $storeMock = $this->createMock(Store::class);

        $storeMock->expects($this->once())->method('isUseStoreInUrl')->willReturn($isUseStoreInUrl);

        $this->storeManager->expects($this->any())->method('getStore')->willReturn($storeMock);
        $this->assertEquals($this->switcher->isStoreInUrl(), $isUseStoreInUrl);
        // check value is cached
        $this->assertEquals($this->switcher->isStoreInUrl(), $isUseStoreInUrl);
    }

    /**
     * @see self::testIsStoreInUrlDataProvider()
     * @return array
     */
    public function isStoreInUrlDataProvider()
    {
        return [[true], [false]];
    }
}
