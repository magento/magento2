<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Dependency\Parser\Composer;

class JsonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    protected $fixtureDir;

    /**
     * @var Json
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
                    ['module' => 'magento/module-module2', 'type' => 'hard'],
                    ['module' => 'magento/module-backend', 'type' => 'soft'],
                ],
            ],
            [
                'name' => 'magento/module-module2',
                'dependencies' => [
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
