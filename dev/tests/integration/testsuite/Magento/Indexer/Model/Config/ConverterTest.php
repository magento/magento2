<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Config;

use Magento\Framework\Exception\ConfigurationMismatchException;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Indexer\Config\Converter
     */
    protected $model;

    protected function setUp()
    {
        $this->model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Indexer\Config\Converter::class);
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

    /**
     * @return void
     */
    public function testConverterWithCircularDependency()
    {
        $pathFiles = __DIR__ . '/_files';
        $path = $pathFiles . '/indexer_with_circular_dependency.xml';
        $domDocument = new \DOMDocument();
        $domDocument->load($path);
        $this->setExpectedException(ConfigurationMismatchException::class, 'Circular dependency references from');
        $this->model->convert($domDocument);
    }

    /**
     * @return void
     */
    public function testConverterWithDependencyOnNotExistingIndexer()
    {
        $pathFiles = __DIR__ . '/_files';
        $path = $pathFiles . '/dependency_on_not_existing_indexer.xml';
        $domDocument = new \DOMDocument();
        $domDocument->load($path);
        $this->setExpectedException(
            ConfigurationMismatchException::class,
            "Dependency declaration 'indexer_4' in 'indexer_2' to the non-existing indexer."
        );
        $this->model->convert($domDocument);
    }
}
