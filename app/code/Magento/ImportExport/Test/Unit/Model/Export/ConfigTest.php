<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Model\Export;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configScopeMock;

    /**
     * @var string
     */
    protected $_cacheId = 'some_id';

    /**
     * @var \Magento\ImportExport\Model\Export\Config
     */
    protected $_model;

    protected function setUp()
    {
        $this->_readerMock = $this->getMock(
            'Magento\ImportExport\Model\Export\Config\Reader',
            [],
            [],
            '',
            false
        );
        $this->_configScopeMock = $this->getMock('Magento\Framework\Config\CacheInterface');
    }

    /**
     * @param array $value
     * @param null|string $expected
     * @dataProvider getEntitiesDataProvider
     */
    public function testGetEntities($value, $expected)
    {
        $this->_configScopeMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            $this->_cacheId
        )->will(
            $this->returnValue(false)
        );
        $this->_readerMock->expects($this->any())->method('read')->will($this->returnValue($value));
        $this->_model = new \Magento\ImportExport\Model\Export\Config(
            $this->_readerMock,
            $this->_configScopeMock,
            $this->_cacheId
        );
        $this->assertEquals($expected, $this->_model->getEntities('entities'));
    }

    /**
     * @return array
     */
    public function getEntitiesDataProvider()
    {
        return [
            'entities_key_exist' => [['entities' => 'value'], 'value'],
            'return_default_value' => [['key_one' => 'value'], null]
        ];
    }

    /**
     * @param array $configData
     * @param string $entity
     * @param string[] $expectedResult
     * @dataProvider getEntityTypesDataProvider
     */
    public function testGetEntityTypes($configData, $entity, $expectedResult)
    {
        $this->_configScopeMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            $this->_cacheId
        )->will(
            $this->returnValue(false)
        );
        $this->_readerMock->expects($this->any())->method('read')->will($this->returnValue($configData));
        $this->_model = new \Magento\ImportExport\Model\Export\Config(
            $this->_readerMock,
            $this->_configScopeMock,
            $this->_cacheId
        );
        $this->assertEquals($expectedResult, $this->_model->getEntityTypes($entity));
    }

    /**
     * @return array
     */
    public function getEntityTypesDataProvider()
    {
        return [
            'valid type' => [
                [
                    'entities' => [
                        'catalog_product' => [
                            'types' => ['configurable', 'simple'],
                        ],
                    ],
                ],
                'catalog_product',
                ['configurable', 'simple'],
            ],
            'not existing entity' => [
                [
                    'entities' => [
                        'catalog_product' => [
                            'types' => ['configurable', 'simple'],
                        ],
                    ],
                ],
                'not existing entity',
                [],
            ],
        ];
    }

    /**
     * @param array $value
     * @param null|string $expected
     * @dataProvider getFileFormatsDataProvider
     */
    public function testGetFileFormats($value, $expected)
    {
        $this->_configScopeMock->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            $this->_cacheId
        )->will(
            $this->returnValue(false)
        );
        $this->_readerMock->expects($this->any())->method('read')->will($this->returnValue($value));
        $this->_model = new \Magento\ImportExport\Model\Export\Config(
            $this->_readerMock,
            $this->_configScopeMock,
            $this->_cacheId
        );
        $this->assertEquals($expected, $this->_model->getFileFormats('fileFormats'));
    }

    /**
     * @return array
     */
    public function getFileFormatsDataProvider()
    {
        return [
            'fileFormats_key_exist' => [['fileFormats' => 'value'], 'value'],
            'return_default_value' => [['key_one' => 'value'], null]
        ];
    }
}
