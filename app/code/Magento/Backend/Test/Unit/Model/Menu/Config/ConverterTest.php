<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Menu\Config;

use Magento\Backend\Model\Menu\Config\Converter;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    /**
     * @var Converter
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = new Converter();
    }

    public function testConvertIfNodeHasAttribute()
    {
        $basePath = realpath(__DIR__) . '/../../_files/';
        $path = $basePath . 'menu_merged.xml';
        $domDocument = new \DOMDocument();
        $domDocument->load($path);
        $expectedData = include $basePath . 'menu_merged.php';
        $this->assertEquals($expectedData, $this->_model->convert($domDocument));
    }
}
