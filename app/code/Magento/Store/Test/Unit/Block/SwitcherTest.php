<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Unit\Block;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SwitcherTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Store\Block\Switcher */
    protected $switcher;

    /** @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Framework\Data\Helper\PostHelper|\PHPUnit_Framework_MockObject_MockObject */
    protected $corePostDataHelper;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManager;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlBuilder;

    protected function setUp()
    {
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)->getMock();
        $this->urlBuilder = $this->getMock(\Magento\Framework\UrlInterface::class);
        $this->context = $this->getMock(\Magento\Framework\View\Element\Template\Context::class, [], [], '', false);
        $this->context->expects($this->any())->method('getStoreManager')->will($this->returnValue($this->storeManager));
        $this->context->expects($this->any())->method('getUrlBuilder')->will($this->returnValue($this->urlBuilder));
        $this->corePostDataHelper = $this->getMock(\Magento\Framework\Data\Helper\PostHelper::class, [], [], '', false);
        $this->switcher = (new ObjectManager($this))->getObject(
            \Magento\Store\Block\Switcher::class,
            [
                'context' => $this->context,
                'postDataHelper' => $this->corePostDataHelper,
            ]
        );
    }

    public function testGetTargetStorePostData()
    {
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)->disableOriginalConstructor()->getMock();
        $store->expects($this->any())->method('getCode')->will($this->returnValue('new-store'));
        $storeSwitchUrl = 'stores/store/switch';
        $this->urlBuilder->expects($this->any())->method('getUrl')->with($storeSwitchUrl)->willReturnArgument(0);
        $this->corePostDataHelper->expects($this->any())->method('getPostData')
            ->with($storeSwitchUrl, ['___store' => 'new-store']);

        $this->switcher->getTargetStorePostData($store);
    }

    /**
     * @dataProvider testIsStoreInUrlDataProvider
     */
    public function testIsStoreInUrl($isUseStoreInUrl)
    {
        $storeMock = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);

        $storeMock->expects($this->once())->method('isUseStoreInUrl')->will($this->returnValue($isUseStoreInUrl));

        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));
        $this->assertEquals($this->switcher->isStoreInUrl(), $isUseStoreInUrl);
        // check value is cached
        $this->assertEquals($this->switcher->isStoreInUrl(), $isUseStoreInUrl);
    }

    /**
     * @see self::testIsStoreInUrlDataProvider()
     * @return array
     */
    public function testIsStoreInUrlDataProvider()
    {
        return [[true], [false]];
    }
}
