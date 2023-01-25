<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\ResourceConnection\Config;

use Magento\Framework\App\ResourceConnection\Config\Converter;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    /**
     * @var Converter
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_filePath;

    /**
     * @var \DOMDocument
     */
    protected $_source;

    protected function setUp(): void
    {
        $this->_filePath = __DIR__ . '/_files/';
        $this->_source = new \DOMDocument();
        $this->_model = new Converter();
    }

    public function testConvert()
    {
        $this->_source->loadXML(file_get_contents($this->_filePath . 'resources.xml'));
        $convertedFile = include $this->_filePath . 'resources.php';
        $this->assertEquals($convertedFile, $this->_model->convert($this->_source));
    }
}
