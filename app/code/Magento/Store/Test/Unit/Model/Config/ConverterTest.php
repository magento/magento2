<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model\Config;

use Magento\Store\Model\Config\Converter;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    /** @var  Converter */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = new Converter();
    }

    public function testConvert()
    {
        $initial = ['path' => ['to' => ['save' => 'saved value', 'overwrite' => 'old value']]];
        $source = ['path/to/overwrite' => 'overwritten', 'path/to/added' => 'added value'];
        $mergeResult = [
            'path' => [
                'to' => ['save' => 'saved value', 'overwrite' => 'overwritten', 'added' => 'added value'],
            ],
        ];
        $this->assertEquals($mergeResult, $this->_model->convert($source, $initial));
    }
}
