<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Structure\Mapper;

class DependenciesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Config\Model\Config\Structure\Mapper\Dependencies
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Config\Model\Config\Structure\Mapper\Dependencies(
            new \Magento\Config\Model\Config\Structure\Mapper\Helper\RelativePathConverter()
        );
    }

    public function testMap()
    {
        $data = require_once realpath(__DIR__ . '/../../../') . '/_files/dependencies_data.php';
        $expected = require_once realpath(__DIR__ . '/../../../') . '/_files/dependencies_mapped.php';

        $actual = $this->_model->map($data);
        $this->assertEquals($expected, $actual);
    }
}
