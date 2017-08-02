<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\Test\Unit\Config;

use Magento\Framework\Exception\ConfigurationMismatchException;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Indexer\Config\Converter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Framework\Indexer\Config\Converter();
    }

    public function testConvert()
    {
        $data = include __DIR__ . '/../_files/indexer_config.php';
        $dom = new \DOMDocument();
        $dom->loadXML($data['inputXML']);

        $this->assertEquals($data['expected'], $this->_model->convert($dom));
    }

    /**
     * @param string $inputXml
     * @param string $exceptionMessage
     * @dataProvider convertWithCircularDependenciesDataProvider
     */
    public function testConvertWithCircularDependencies($inputXml, $exceptionMessage)
    {
        $dom = new \DOMDocument();
        $dom->loadXML($inputXml);
        $this->setExpectedException(ConfigurationMismatchException::class, $exceptionMessage);
        $this->_model->convert($dom);
    }

    /**
     * @return array
     */
    public function convertWithCircularDependenciesDataProvider()
    {
        return [
            'Circular dependency on the first level' => [
                'inputXML' => '<?xml version="1.0" encoding="UTF-8"?><config>'
                    . '<indexer id="indexer_1"><dependencies><indexer id="indexer_2"/></dependencies></indexer>'
                    . '<indexer id="indexer_2"><dependencies><indexer id="indexer_1"/></dependencies></indexer>'
                    . '</config>',
                'exceptionMessage' => "Circular dependency references from 'indexer_2' to 'indexer_1'.",
            ],
            'Circular dependency a deeper than the first level' => [
                'inputXML' => '<?xml version="1.0" encoding="UTF-8"?><config>'
                    . '<indexer id="indexer_1"><dependencies><indexer id="indexer_2"/></dependencies></indexer>'
                    . '<indexer id="indexer_2"><dependencies><indexer id="indexer_3"/></dependencies></indexer>'
                    . '<indexer id="indexer_3"><dependencies><indexer id="indexer_4"/></dependencies></indexer>'
                    . '<indexer id="indexer_4"><dependencies><indexer id="indexer_1"/></dependencies></indexer>'
                    . '</config>',
                'exceptionMessage' => "Circular dependency references from 'indexer_4' to 'indexer_1'.",
            ],
        ];
    }

    /**
     * @param string $inputXml
     * @param string $exceptionMessage
     * @dataProvider convertWithDependencyOnNotExistingIndexerDataProvider
     */
    public function testConvertWithDependencyOnNotExistingIndexer($inputXml, $exceptionMessage)
    {
        $dom = new \DOMDocument();
        $dom->loadXML($inputXml);
        $this->setExpectedException(ConfigurationMismatchException::class, $exceptionMessage);
        $this->_model->convert($dom);
    }

    /**
     * @return array
     */
    public function convertWithDependencyOnNotExistingIndexerDataProvider()
    {
        return [
            [
                'inputXML' => '<?xml version="1.0" encoding="UTF-8"?><config>'
                    . '<indexer id="indexer_1"><dependencies><indexer id="indexer_3"/></dependencies></indexer>'
                    . '<indexer id="indexer_2"><dependencies><indexer id="indexer_1"/></dependencies></indexer>'
                    . '</config>',
                'exceptionMessage' => "Dependency declaration 'indexer_3' in 'indexer_1' to the non-existing indexer.",
            ],
        ];
    }
}
