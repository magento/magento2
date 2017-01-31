<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Backend\Block\Widget\Button
 */
namespace Magento\Backend\Test\Unit\Block\Widget;

class ButtonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_layoutMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_blockMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_buttonMock;

    protected function setUp()
    {
        $this->_layoutMock = $this->getMock('Magento\Framework\View\Layout', [], [], '', false, false);

        $arguments = [
            'urlBuilder' => $this->getMock('Magento\Backend\Model\Url', [], [], '', false, false),
            'layout' => $this->_layoutMock,
        ];

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_blockMock = $objectManagerHelper->getObject('Magento\Backend\Block\Widget\Button', $arguments);
    }

    protected function tearDown()
    {
        unset($this->_layoutMock);
        unset($this->_buttonMock);
    }

    /**
     * @covers \Magento\Backend\Block\Widget\Button::getAttributesHtml
     * @dataProvider getAttributesHtmlDataProvider
     */
    public function testGetAttributesHtml($data, $expect)
    {
        $this->_blockMock->setData($data);
        $attributes = $this->_blockMock->getAttributesHtml();
        $this->assertRegExp($expect, $attributes);
    }

    public function getAttributesHtmlDataProvider()
    {
        return [
            [
                ['data_attribute' => ['validation' => ['required' => true]]],
                '/data-validation="[^"]*" /',
            ],
            [
                ['data_attribute' => ['mage-init' => ['button' => ['someKey' => 'someValue']]]],
                '/data-mage-init="[^"]*" /'
            ],
            [
                [
                    'data_attribute' => [
                        'mage-init' => ['button' => ['someKey' => 'someValue']],
                        'validation' => ['required' => true],
                    ],
                ],
                '/data-mage-init="[^"]*" data-validation="[^"]*" /'
            ]
        ];
    }
}
