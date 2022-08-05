<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Framework\DataObject;
use Magento\Framework\Phrase;
use Magento\Framework\Phrase\RendererInterface;

class TextTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var RendererInterface
     */
    private $origRenderer;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->origRenderer = Phrase::getRenderer();
        /** @var RendererInterface|PHPUnit\Framework\MockObject_MockObject $rendererMock */
        $rendererMock = $this->getMockForAbstractClass(RendererInterface::class);
        $rendererMock->expects($this->any())
            ->method('render')
            ->willReturnCallback(
                function ($input) {
                    return end($input) . ' translated';
                }
            );
        Phrase::setRenderer($rendererMock);
    }

    protected function tearDown(): void
    {
        Phrase::setRenderer($this->origRenderer);
    }

    /**
     * @param array $columnData
     * @param array $rowData
     * @param string $expected
     * @dataProvider renderDataProvider
     */
    public function testRender($columnData, $rowData, $expected)
    {
        /** @var Text $renderer */
        $renderer = $this->objectManager->create(Text::class);
        /** @var Column $column */
        $column = $this->objectManager->create(
            Column::class,
            [
                'data' => $columnData
            ]
        );
        /** @var DataObject $row */
        $row = $this->objectManager->create(
            DataObject::class,
            [
                'data' => $rowData
            ]
        );
        $this->assertEquals(
            $expected,
            $renderer->setColumn($column)->render($row)
        );
    }

    /**
     * @return array
     */
    public function renderDataProvider()
    {
        return [
            [
                [
                    'index' => 'title',
                    'translate' => true
                ],
                [
                    'title' => 'String'
                ],
                'String translated'
            ],
            [
                [
                    'index' => 'title'
                ],
                [
                    'title' => 'Doesn\'t need to be translated'
                ],
                'Doesn&#039;t need to be translated'
            ],
            [
                [
                    'format' => '#$subscriber_id $customer_name ($subscriber_email)'
                ],
                [
                    'subscriber_id' => '10',
                    'customer_name' => 'John Doe',
                    'subscriber_email' => 'john@doe.com'
                ],
                '#10 John Doe (john@doe.com)'
            ],
            [
                [
                    'format' => '$customer_name, email: $subscriber_email',
                    'translate' => true
                ],
                [
                    'customer_name' => 'John Doe',
                    'subscriber_email' => 'john@doe.com'
                ],
                'John Doe, email: john@doe.com translated'
            ],
            [
                [
                    'format' => 'String',
                    'translate' => true
                ],
                [],
                'String translated'
            ],
            [
                [
                    'format' => 'Doesn\'t need to be translated'
                ],
                [],
                'Doesn&#039;t need to be translated'
            ]
        ];
    }
}
