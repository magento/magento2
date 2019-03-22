<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Ui\Component\Listing\Column;

use Magento\Cms\Ui\Component\Listing\Column\BlockActions;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * BlockActionsTest contains unit tests for \Magento\Cms\Ui\Component\Listing\Column\BlockActions class.
 */
class BlockActionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BlockActions
     */
    private $blockActions;

    /**
     * @var Escaper|MockObject
     */
    private $escaper;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $context = $this->createMock(ContextInterface::class);

        $processor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->never())
            ->method('getProcessor')
            ->willReturn($processor);

        $this->urlBuilder = $this->createMock(UrlInterface::class);

        $this->escaper = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->setMethods(['escapeHtmlAttr'])
            ->getMock();

        $this->blockActions = $objectManager->getObject(BlockActions::class, [
            'context' => $context,
            'urlBuilder' => $this->urlBuilder
        ]);

        $objectManager->setBackwardCompatibleProperty($this->blockActions, 'escaper', $this->escaper);
    }

    /**
     * Unit test for prepareDataSource method.
     *
     * @covers \Magento\Cms\Ui\Component\Listing\Column\BlockActions::prepareDataSource
     * @return void
     */
    public function testPrepareDataSource()
    {
        $blockId = 1;
        $title = 'block title';
        $items = [
            'data' => [
                'items' => [
                    [
                        'block_id' => $blockId,
                        'title' => $title,
                    ],
                ],
            ],
        ];
        $name = 'item_name';
        $expectedItems = [
            [
                'block_id' => $blockId,
                'title' => $title,
                $name => [
                    'edit' => [
                        'href' => 'test/url/edit',
                        'label' => __('Edit'),
                    ],
                    'delete' => [
                        'href' => 'test/url/delete',
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete %1', $title),
                            'message' => __('Are you sure you want to delete a %1 record?', $title),
                        ],
                        'post' => true,
                    ],
                ],
            ],
        ];

        $this->escaper->expects($this->once())
            ->method('escapeHtmlAttr')
            ->with($title)
            ->willReturn($title);

        $this->urlBuilder->expects($this->exactly(2))
            ->method('getUrl')
            ->willReturnMap(
                [
                    [
                        BlockActions::URL_PATH_EDIT,
                        [
                            'block_id' => $blockId,
                        ],
                        'test/url/edit',
                    ],
                    [
                        BlockActions::URL_PATH_DELETE,
                        [
                            'block_id' => $blockId,
                        ],
                        'test/url/delete',
                    ],
                ]
            );

        $this->blockActions->setData('name', $name);

        $actual = $this->blockActions->prepareDataSource($items);
        $this->assertEquals($expectedItems, $actual['data']['items']);
    }
}
