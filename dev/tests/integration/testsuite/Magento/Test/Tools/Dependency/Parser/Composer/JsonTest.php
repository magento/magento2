<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Tools\Dependency\Parser\Composer;

use Magento\Tools\Dependency\Parser\Composer\Json;

class JsonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $fixtureDir;

    /**
     * @var \Magento\Tools\Dependency\Parser\Composer\Json
     */
    protected $parser;

    protected function setUp()
    {
        $this->fixtureDir = realpath(__DIR__ . '/../../_files') . '/';

        $this->parser = new Json();
    }

    public function testParse()
    {
        $expected = [
            [
                'name' => 'magento/module-module1',
                'dependencies' => [
                    ['module' => 'magento/module-core', 'type' => 'hard'],
                    ['module' => 'magento/module-module2', 'type' => 'hard'],
                    ['module' => 'magento/module-backend', 'type' => 'soft'],
                ],
            ],
            [
                'name' => 'magento/module-module2',
                'dependencies' => [
                    ['module' => 'magento/module-core', 'type' => 'hard'],
                    ['module' => 'magento/module-module3', 'type' => 'hard'],
                ]
            ],
        ];

        $actual = $this->parser->parse(
            [
                'files_for_parse' => [
                    $this->fixtureDir . 'composer1.json',
                    $this->fixtureDir . 'composer2.json',
                ],
            ]
        );

        $this->assertEquals($expected, $actual);
    }
}
