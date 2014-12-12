<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Search\Request\Config;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Converter */
    protected $object;

    protected function setUp()
    {
        $this->object = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\Search\Request\Config\Converter');
    }

    public function testConvert()
    {
        $document = new \DOMDocument();
        $document->load(__DIR__ . '../../../_files/search_request.xml');
        $result = $this->object->convert($document);
        $expected = include __DIR__ . '/../../_files/search_request_config.php';
        $this->assertEquals($expected, $result);
    }
}
