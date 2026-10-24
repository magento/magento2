<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Translate\Js;

use Magento\Framework\Translate\Js\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @return void
     */
    public function testDefault()
    {
        $config = new Config();
        $this->assertFalse($config->dictionaryEnabled());
        $this->assertNull($config->getDictionaryFileName());
    }

    /**
     * @return void
     */
    public function testCustom()
    {
        $path = 'path';
        $config = new Config(true, $path);
        $this->assertTrue($config->dictionaryEnabled());
        $this->assertEquals($path, $config->getDictionaryFileName());
    }
}
