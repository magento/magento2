<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Test\Unit\Ui\Component\Listing\Column;

use Magento\AsynchronousOperations\Model\BulkSummary;

class ActionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uiComponentFactory;

    /**
     * @var \Magento\AsynchronousOperations\Ui\Component\Listing\Column\Actions
     */
    private $actionColumn;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->context = $this->createMock(\Magento\Framework\View\Element\UiComponent\ContextInterface::class);
        $this->uiComponentFactory = $this->createMock(\Magento\Framework\View\Element\UiComponentFactory::class);
        $processor = $this->createPartialMock(
            \Magento\Framework\View\Element\UiComponent\Processor::class,
            ['getProcessor']
        );
        $this->context->expects($this->never())->method('getProcessor')->will($this->returnValue($processor));
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->actionColumn = $objectManager->getObject(
            \Magento\AsynchronousOperations\Ui\Component\Listing\Column\Actions::class,
            [
                'context' => $this->context,
                'uiComponentFactory' => $this->uiComponentFactory,
                'components' => [],
                'data' => ['name' => 'Edit'],
                'editUrl' => ''
            ]
        );
    }

    /**
     * Test for method prepareDataSource
     */
    public function testPrepareDataSource()
    {
        $href = 'bulk/bulk/details/id/bulk-1';
        $this->context->expects($this->once())->method('getUrl')->with(
            'bulk/bulk/details',
            ['uuid' => 'bulk-1']
        )->willReturn($href);
        $dataSource['data']['items']['item'] = [BulkSummary::BULK_ID => 'bulk-1'];
        $actionColumn['data']['items']['item'] = [
            'Edit' => [
                'edit' => [
                    'href' => $href,
                    'label' => __('Details'),
                    'hidden' => false
                ]
            ]
        ];
        $expectedResult = array_merge_recursive($dataSource, $actionColumn);
        $this->assertEquals($expectedResult, $this->actionColumn->prepareDataSource($dataSource));
    }
}
