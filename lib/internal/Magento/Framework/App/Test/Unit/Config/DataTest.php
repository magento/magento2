<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Config;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Config\Data
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Config\MetadataProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_metaDataProcessor;

    protected function setUp()
    {
        $this->_metaDataProcessor = $this->getMock(
            'Magento\Framework\App\Config\MetadataProcessor',
            [],
            [],
            '',
            false
        );
        $this->_metaDataProcessor->expects($this->any())->method('process')->will($this->returnArgument(0));
        $this->_model = new \Magento\Framework\App\Config\Data($this->_metaDataProcessor, []);
    }

    /**
     * @param string $path
     * @param mixed $value
     * @dataProvider setValueDataProvider
     */
    public function testSetValue($path, $value)
    {
        $this->_model->setValue($path, $value);
        $this->assertEquals($value, $this->_model->getValue($path));
    }

    public function setValueDataProvider()
    {
        return [
            'simple value' => ['some/config/value', 'test'],
            'complex value' => ['some/config/value', ['level1' => ['level2' => 'test']]]
        ];
    }

    public function testGetData()
    {
        $model = new \Magento\Framework\App\Config\Data(
            $this->_metaDataProcessor,
            ['test' => ['path' => 'value']]
        );
        $this->assertEquals('value', $model->getValue('test/path'));
    }
}
