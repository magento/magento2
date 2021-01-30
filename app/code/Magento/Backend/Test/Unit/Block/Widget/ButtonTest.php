<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Backend\Block\Widget\Button
 */
namespace Magento\Backend\Test\Unit\Block\Widget;

use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Model\Url;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ButtonTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_layoutMock;

    /**
     * @var MockObject
     */
    protected $_factoryMock;

    /**
     * @var MockObject
     */
    protected $_blockMock;

    /**
     * @var MockObject
     */
    protected $_buttonMock;

    protected function setUp(): void
    {
        $this->_layoutMock = $this->createMock(Layout::class);

        $arguments = [
            'urlBuilder' => $this->createMock(Url::class),
            'layout' => $this->_layoutMock,
        ];

        $objectManagerHelper = new ObjectManager($this);
        $this->_blockMock = $objectManagerHelper->getObject(Button::class, $arguments);
    }

    protected function tearDown(): void
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
        $this->assertMatchesRegularExpression($expect, $attributes);
    }

    /**
     * @return array
     */
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

    /**
     * Verifies ability of adding button onclick attribute
     *
     * @return void
     */
    public function testOnClickAttribute(): void
    {
        $this->_blockMock->setData(['onclick_attribute' => 'value']);
        $attributes = $this->_blockMock->getAttributesHtml();
        $this->assertStringContainsString('onclick', $attributes);
    }
}
