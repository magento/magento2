<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\View;

class HistoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Helper\Admin|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adminHelperMock;

    /**
     * @var \Magento\Sales\Block\Adminhtml\Order\View\History
     */
    protected $viewHistory;

    protected function setUp()
    {
        $this->adminHelperMock = $this->getMockBuilder(\Magento\Sales\Helper\Admin::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->viewHistory = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            \Magento\Sales\Block\Adminhtml\Order\View\History::class,
            [
                'adminHelper' => $this->adminHelperMock
            ]
        );
    }

    /**
     * @param string $data
     * @param string $expected
     * @param null|array $allowedTags
     * @dataProvider escapeHtmlDataProvider
     */
    public function testEscapeHtml($data, $expected, $allowedTags = null)
    {
        $this->adminHelperMock
            ->expects($this->any())
            ->method('escapeHtmlWithLinks')
            ->will($this->returnValue($expected));
        $actual = $this->viewHistory->escapeHtml($data, $allowedTags);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function escapeHtmlDataProvider()
    {
        return [
            [
                '<a>some text in tags</a>',
                '&lt;a&gt;some text in tags&lt;/a&gt;',
                'allowedTags' => null
            ],
            [
                'Transaction ID: "<a target="_blank" href="https://www.paypal.com/?id=XX123XX">XX123XX</a>"',
                'Transaction ID: &quot;<a target="_blank" href="https://www.paypal.com/?id=XX123XX">XX123XX</a>&quot;',
                'allowedTags' => ['b', 'br', 'strong', 'i', 'u', 'a']
            ],
            [
                '<a>some text in tags</a>',
                '<a>some text in tags</a>',
                'allowedTags' => ['a']
            ]
        ];
    }
}
