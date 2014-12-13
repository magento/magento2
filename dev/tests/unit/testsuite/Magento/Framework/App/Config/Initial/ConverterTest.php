<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\App\Config\Initial;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Config\Initial\Converter
     */
    protected $_model;

    protected function setUp()
    {
        $nodeMap = [
            'default' => '/config/default',
            'stores' => '/config/stores',
            'websites' => '/config/websites',
        ];
        $this->_model = new \Magento\Framework\App\Config\Initial\Converter($nodeMap);
    }

    public function testConvert()
    {
        $fixturePath = __DIR__ . '/_files/';
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($fixturePath . 'config.xml'));
        $expectedResult = include $fixturePath . 'converted_config.php';
        $this->assertEquals($expectedResult, $this->_model->convert($dom));
    }
}
