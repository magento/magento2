<?php
declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Framework\DataObject;
use Magento\Framework\Phrase;
use Magento\Framework\Phrase\RendererInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

class ActionTest extends \PHPUnit\Framework\TestCase
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
        $renderer = $this->objectManager->create(Action::class);
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
        $this->assertStringContainsString(
            $expected,
            $renderer->setColumn($column)->render($row)
        );
    }

    /**
     * @return array
     */
    public static function renderDataProvider(): array
    {
        return [
            [
                [
                    'index' => 'type',
                    'type' => 'action',
                    'actions' => [
                        'rollback_action'=> [
                            'caption' => 'Rollback', 'href'=>'#', 'onclick' => 'alert("test")'
                        ]
                    ]
                ],
                [],
                'alert("test")'
            ],
        ];
    }
}
