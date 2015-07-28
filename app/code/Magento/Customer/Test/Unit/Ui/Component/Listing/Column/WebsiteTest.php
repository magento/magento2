<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Ui\Component\Listing\Column;

use Magento\Customer\Ui\Component\Listing\Column\Website;

class WebsiteTest extends \PHPUnit_Framework_TestCase
{
    /** @var Website */
    protected $component;

    /** @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $uiComponentFactory;

    /** @var \Magento\Store\Model\WebsiteFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $websiteFactory;

    /** @var \Magento\Store\Model\Website|\PHPUnit_Framework_MockObject_MockObject */
    protected $website;

    public function setup()
    {
        $this->context = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\ContextInterface',
            [],
            '',
            false
        );
        $this->uiComponentFactory = $this->getMock(
            'Magento\Framework\View\Element\UiComponentFactory',
            [],
            [],
            '',
            false
        );
        $this->websiteFactory = $this->getMock(
            'Magento\Store\Model\WebsiteFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->website = $this->getMock('Magento\Store\Model\Website', ['load', 'getName'], [], '', false);
        $this->component = new Website(
            $this->context,
            $this->uiComponentFactory,
            $this->websiteFactory
        );
        $this->component->setData('name', 'website_id');
    }

    public function testPrepareDataSource()
    {
        $this->websiteFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->website);
        $this->website->expects($this->once())
            ->method('load')
            ->with(1)
            ->willReturnSelf();
        $this->website->expects($this->once())
            ->method('getName')
            ->willReturn('Main');

        $dataSource = [
            'data' => [
                'items' => [
                    [
                        'website_id' => 1
                    ],
                ]
            ]
        ];
        $expectedDataSource =  [
            'data' => [
                'items' => [
                    [
                        'website_id' => 'Main'
                    ],
                ]
            ]
        ];

        $this->component->prepareDataSource($dataSource);

        $this->assertEquals($expectedDataSource, $dataSource);
    }
}
