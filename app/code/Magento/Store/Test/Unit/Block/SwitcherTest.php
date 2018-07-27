<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
        $this->storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')->getMock();
        $this->urlBuilder = $this->getMockBuilder('Magento\Framework\UrlInterface')->getMock();
        $this->context = $this->getMockBuilder('Magento\Framework\View\Element\Template\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())->method('getStoreManager')->will($this->returnValue($this->storeManager));
        $this->context->expects($this->any())->method('getUrlBuilder')->will($this->returnValue($this->urlBuilder));
        $this->corePostDataHelper = $this->getMock('Magento\Framework\Data\Helper\PostHelper', [], [], '', false);
        $this->switcher = (new ObjectManager($this))->getObject(
            'Magento\Store\Block\Switcher',
            [
                'context' => $this->context,
                'postDataHelper' => $this->corePostDataHelper,
            ]
        );
    }

    public function testGetTargetStorePostData()
    {
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $store->expects($this->any())
            ->method('getCode')
            ->willReturn('new-store');
        $storeSwitchUrl = 'http://domain.com/stores/store/switch';
        $store->expects($this->once())
            ->method('getCurrentUrl')
            ->with(false)
            ->willReturn($storeSwitchUrl);
        $this->corePostDataHelper->expects($this->any())
            ->method('getPostData')
            ->with($storeSwitchUrl, ['___store' => 'new-store']);

        $this->switcher->getTargetStorePostData($store);
    }

    /**
     * @dataProvider testIsStoreInUrlDataProvider
     */
    public function testIsStoreInUrl($isUseStoreInUrl)
    {
        $storeMock = $this->getMock('Magento\Store\Model\Store', [], [], '', false);

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
