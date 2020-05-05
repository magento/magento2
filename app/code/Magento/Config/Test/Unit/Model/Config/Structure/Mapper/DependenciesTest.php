<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Structure\Mapper;

use Magento\Config\Model\Config\Structure\Mapper\Dependencies;
use Magento\Config\Model\Config\Structure\Mapper\Helper\RelativePathConverter;
use PHPUnit\Framework\TestCase;

class DependenciesTest extends TestCase
{
    /**
     * @var Dependencies
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = new Dependencies(
            new RelativePathConverter()
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
