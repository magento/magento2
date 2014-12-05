<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
                ]
            ],
            [
                'name' => 'magento/module-module2',
                'dependencies' => [
                    ['module' => 'magento/module-core', 'type' => 'hard'],
                    ['module' => 'magento/module-module3', 'type' => 'hard']
                ]
            ]
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
