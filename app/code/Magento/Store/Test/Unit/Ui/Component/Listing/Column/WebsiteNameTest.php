<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Unit\Ui\Component\Listing\Column;

use Magento\Store\Ui\Component\Listing\Column\WebsiteName;

/**
 * Class WebsiteNameTest contains unit tests for \Magento\Store\Ui\Component\Listing\Column\WebsiteName
 */
class WebsiteNameTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var WebsiteName
     */
    private $component;

    /**
     * @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uiComponentFactory;

    public function setUp()
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->getMockForAbstractClass();
        $processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->never())->method('getProcessor')->willReturn($processor);
        $this->uiComponentFactory = $this->createMock(\Magento\Framework\View\Element\UiComponentFactory::class);
        $this->component = new WebsiteName(
            $this->context,
            $this->uiComponentFactory
        );

        $this->component->setData('name', 'name');
    }

    /**
     * @covers \Magento\Store\Ui\Component\Listing\Column\WebsiteName::prepareDataSource
     */
    public function testPrepareDataSource()
    {
        $dataSource = [
            'data' => [
                'items' => [
                    [
                        'name' => 'Main Website',
                        'website_id' => 1,
                        'code' => 'base',
                    ]
                ]
            ]
        ];

        $title = sprintf(
            '<a title="Edit Store" href="%s">Main Website</a><br />(Code: base)',
            'http://magento-2-1.dev/admin/system_store/editWebsite'
        );

        $expectedDataSource = [
            'data' => [
                'items' => [
                    [
                        'name' => $title,
                        'website_id' => 1,
                        'code' => 'base',
                    ]
                ]
            ]
        ];

        $this->context->expects($this->once())
            ->method('getUrl')
            ->with(
                'adminhtml/system_store/editWebsite',
                ['website_id' => 1]
            )
            ->willReturn('http://magento-2-1.dev/admin/system_store/editWebsite');

        $dataSource = $this->component->prepareDataSource($dataSource);
        $this->assertEquals($expectedDataSource, $dataSource);
    }
}
