<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Config;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Indexer\Model\Config\Converter
     */
    protected $model;

    protected function setUp()
    {
        $this->model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('\Magento\Indexer\Model\Config\Converter');
    }

    public function testConverter()
    {
        $pathFiles = __DIR__ . '/_files';
        $expectedResult = require $pathFiles . '/result.php';
        $path = $pathFiles . '/indexer.xml';
        $domDocument = new \DOMDocument();
        $domDocument->load($path);
        $result = $this->model->convert($domDocument);
        $this->assertEquals($expectedResult, $result);
    }
}
