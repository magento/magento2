<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Test\Unit\Block\Adminhtml\Template\Grid\Renderer;

use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Newsletter\Block\Adminhtml\Template\Grid\Renderer\Sender;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Newsletter\Block\Adminhtml\Template\Grid\Renderer\Sender.
 */
class SenderTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Sender
     */
    private $sender;

    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $escaper = $this->objectManagerHelper->getObject(
            Escaper::class
        );
        $this->sender = $this->objectManagerHelper->getObject(
            Sender::class,
            [
                'escaper' => $escaper
            ]
        );
    }

    /**
     * @param array $expectedSender
     * @param array $passedSender
     *
     * @return void
     */
    #[DataProvider('rendererDataProvider')]
    public function testRender(array $passedSender, array $expectedSender)
    {
        $row = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getTemplateSenderName', 'getTemplateSenderEmail']
        );
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
    public static function rendererDataProvider()
    {
        return [
            [
                [
                    'sender' => 'Sender',
                    'sender_email' => 'sender@example.com',
                ],
                [
                    'sender' => 'Sender',
                    'sender_email' => 'sender@example.com',
                ],
            ],
            [
                [
                    'sender' => "<br>'Sender'</br>",
                    'sender_email' => "<br>'email@example.com'</br>",
                ],
                [
                    'sender' => "&lt;br&gt;&#039;Sender&#039;&lt;/br&gt;",
                    'sender_email' => "&lt;br&gt;&#039;email@example.com&#039;&lt;/br&gt;",
                ],
            ],
            [
                [
                    'sender' => '"<script>alert(document.domain)</script>"@example.com',
                    'sender_email' => '"<script>alert(document.domain)</script>"@example.com',
                ],
                [
                    'sender' => '&quot;&lt;script&gt;alert(document.domain)&lt;/script&gt;&quot;@example.com',
                    'sender_email' => '&quot;&lt;script&gt;alert(document.domain)&lt;/script&gt;&quot;@example.com',
                ],
            ],
        ];
    }
}
