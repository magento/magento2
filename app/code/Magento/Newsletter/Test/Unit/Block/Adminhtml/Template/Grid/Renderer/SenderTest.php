<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Test\Unit\Block\Adminhtml\Template\Grid\Renderer;

/**
 * Class for \Magento\Newsletter\Block\Adminhtml\Template\Grid\Renderer\Sender tests
 */
class SenderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Newsletter\Block\Adminhtml\Template\Grid\Renderer\Sender */
    protected $sender;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManagerHelper;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->sender = $this->objectManagerHelper->getObject(
            \Magento\Newsletter\Block\Adminhtml\Template\Grid\Renderer\Sender::class
        );
    }

    /**
     * @dataProvider rendererDataProvider
     * @param array $expectedSender
     * @param array $passedSender
     */
    public function testRender($passedSender, $expectedSender)
    {
        $row = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(['getTemplateSenderName', 'getTemplateSenderEmail'])
            ->getMock();
        $row->expects($this->atLeastOnce())->method('getTemplateSenderName')
            ->willReturn($passedSender['sender']);
        $row->expects($this->atLeastOnce())->method('getTemplateSenderEmail')
            ->willReturn($passedSender['sender_email']);
        $this->assertEquals(
            $expectedSender['sender'] . ' [' . $expectedSender['sender_email'] . ']',
            $this->sender->render($row)
        );
    }

    /**
     * @return array
     */
    public function rendererDataProvider()
    {
        return [
            [
                [
                    'sender' => 'Sender',
                    'sender_email' => 'sender@example.com'
                ],
                [
                    'sender' => 'Sender',
                    'sender_email' => 'sender@example.com'
                ]
            ],
            [
                [
                    'sender' => "<br>'Sender'</br>",
                    'sender_email' => "<br>'email@example.com'</br>"
                ],
                [
                    'sender' => "&lt;br&gt;'Sender'&lt;/br&gt;",
                    'sender_email' => "&lt;br&gt;'email@example.com'&lt;/br&gt;"
                ]
            ]
        ];
    }
}
