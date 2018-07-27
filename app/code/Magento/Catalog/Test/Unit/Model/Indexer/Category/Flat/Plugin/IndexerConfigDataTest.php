<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Flat\Plugin;

class IndexerConfigDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\Plugin\IndexerConfigData
     */
    protected $model;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->stateMock = $this->getMock(
            'Magento\Catalog\Model\Indexer\Category\Flat\State',
            ['isFlatEnabled'],
            [],
            '',
            false
        );

        $this->subjectMock = $this->getMock('Magento\Indexer\Model\Config\Data', [], [], '', false);

        $this->model = new \Magento\Catalog\Model\Indexer\Category\Flat\Plugin\IndexerConfigData($this->stateMock);
    }

    /**
     * @param bool $isFlat
     * @param string $path
     * @param mixed $default
     * @param array $inputData
     * @param array $outputData
     * @dataProvider aroundGetDataProvider
     */
    public function testAroundGet($isFlat, $path, $default, $inputData, $outputData)
    {
        $this->stateMock->expects($this->once())->method('isFlatEnabled')->will($this->returnValue($isFlat));
        $closureMock = function () use ($inputData) {
            return $inputData;
        };
        $this->assertEquals($outputData, $this->model->aroundGet($this->subjectMock, $closureMock, $path, $default));
    }

    /**
     * @return array
     */
    public function aroundGetDataProvider()
    {
        $flatIndexerData = [
            'indexer_id' => 'catalog_category_flat',
            'action' => '\Action\Class',
            'title' => 'Title',
            'description' => 'Description',
        ];
        $otherIndexerData = [
            'indexer_id' => 'other_indexer',
            'action' => '\Action\Class',
            'title' => 'Title',
            'description' => 'Description',
        ];
        return [
            // flat is enabled, nothing is being changed
            [
                true,
                null,
                null,
                ['catalog_category_flat' => $flatIndexerData, 'other_indexer' => $otherIndexerData],
                ['catalog_category_flat' => $flatIndexerData, 'other_indexer' => $otherIndexerData]
            ],
            // flat is disabled, path is absent, flat indexer is being removed
            [
                false,
                null,
                null,
                ['catalog_category_flat' => $flatIndexerData, 'other_indexer' => $otherIndexerData],
                ['other_indexer' => $otherIndexerData]
            ],
            // flat is disabled, path is null, flat indexer is being removed
            [
                false,
                null,
                null,
                ['catalog_category_flat' => $flatIndexerData, 'other_indexer' => $otherIndexerData],
                ['other_indexer' => $otherIndexerData]
            ],
            // flat is disabled, path is flat indexer, flat indexer is being removed
            [false, 'catalog_category_flat', null, $flatIndexerData, null],
            // flat is disabled, path is flat indexer, default is array(), flat indexer is being array()
            [false, 'catalog_category_flat', null, $flatIndexerData, null],
            // flat is disabled, path is other indexer, nothing is being changed
            [false, 'other_indexer', null, $otherIndexerData, $otherIndexerData]
        ];
    }
}
