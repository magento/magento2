<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Config;

use Magento\Framework\App\Config\Data;
use Magento\Framework\App\Config\MetadataProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var Data
     */
    protected $_model;

    /**
     * @var MetadataProcessor|MockObject
     */
    protected $_metaDataProcessor;

    protected function setUp(): void
    {
        $this->_metaDataProcessor = $this->createMock(MetadataProcessor::class);
        $this->_metaDataProcessor->expects($this->any())->method('process')->willReturnArgument(0);
        $this->_model = new Data($this->_metaDataProcessor, []);
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

    /**
     * @return array
     */
    public function setValueDataProvider()
    {
        return [
            'simple value' => ['some/config/value', 'test'],
            'complex value' => ['some/config/value', ['level1' => ['level2' => 'test']]]
        ];
    }

    public function testGetData()
    {
        $model = new Data(
            $this->_metaDataProcessor,
            ['test' => ['path' => 'value']]
        );
        $this->assertEquals('value', $model->getValue('test/path'));
    }
}
