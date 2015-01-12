<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Plugin;

class IndexerConfigDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Plugin\IndexerConfigData
     */
    protected $model;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_stateMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->_stateMock = $this->getMock(
            'Magento\Catalog\Model\Indexer\Product\Flat\State',
            ['isFlatEnabled'],
            [],
            '',
            false
        );
        $this->subjectMock = $this->getMock('Magento\Indexer\Model\Config\Data', [], [], '', false);

        $this->model = new \Magento\Catalog\Model\Indexer\Product\Flat\Plugin\IndexerConfigData($this->_stateMock);
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
        $closureMock = function () use ($inputData) {
            return $inputData;
        };
        $this->_stateMock->expects($this->once())->method('isFlatEnabled')->will($this->returnValue($isFlat));

        $this->assertEquals($outputData, $this->model->aroundGet($this->subjectMock, $closureMock, $path, $default));
    }

    public function aroundGetDataProvider()
    {
        $flatIndexerData = [
            'indexer_id' => 'catalog_product_flat',
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
                ['catalog_product_flat' => $flatIndexerData, 'other_indexer' => $otherIndexerData],
                ['catalog_product_flat' => $flatIndexerData, 'other_indexer' => $otherIndexerData]
            ],
            // flat is disabled, path is absent, flat indexer is being removed
            [
                false,
                null,
                null,
                ['catalog_product_flat' => $flatIndexerData, 'other_indexer' => $otherIndexerData],
                ['other_indexer' => $otherIndexerData]
            ],
            // flat is disabled, path is null, flat indexer is being removed
            [
                false,
                null,
                null,
                ['catalog_product_flat' => $flatIndexerData, 'other_indexer' => $otherIndexerData],
                ['other_indexer' => $otherIndexerData]
            ],
            // flat is disabled, path is flat indexer, flat indexer is being removed
            [false, 'catalog_product_flat', null, $flatIndexerData, null],
            // flat is disabled, path is flat indexer, default is array(), flat indexer is being array()
            [false, 'catalog_product_flat', null, $flatIndexerData, null],
            // flat is disabled, path is other indexer, nothing is being changed
            [false, 'other_indexer', null, $otherIndexerData, $otherIndexerData]
        ];
    }
}
