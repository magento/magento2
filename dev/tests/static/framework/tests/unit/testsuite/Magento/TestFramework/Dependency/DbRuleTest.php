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
namespace Magento\TestFramework\Dependency;

class DbRuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DbRule
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new DbRule(array('some_table' => 'SomeModule'));
    }

    /**
     * @param string $module
     * @param string $file
     * @param string $contents
     * @param array $expected
     * @dataProvider getDependencyInfoDataProvider
     */
    public function testGetDependencyInfo($module, $file, $contents, array $expected)
    {
        $this->assertEquals($expected, $this->model->getDependencyInfo($module, 'any', $file, $contents));
    }

    public function getDependencyInfoDataProvider()
    {
        return array(
            array('any', 'non-resource-file-path.php', 'any', array()),
            array(
                'any',
                '/app/some/path/sql/some-file.php',
                '$install->getTableName("unknown_table")',
                array(array('module' => 'Unknown', 'source' => 'unknown_table'))
            ),
            array(
                'any',
                '/app/some/path/data/some-file.php',
                '$install->getTableName("unknown_table")',
                array(array('module' => 'Unknown', 'source' => 'unknown_table'))
            ),
            array(
                'SomeModule',
                '/app/some/path/resource/some-file.php',
                '$install->getTableName("some_table")',
                array()
            ),
            array(
                'any',
                '/app/some/path/resource/some-file.php',
                '$install->getTableName(\'some_table\')',
                array(
                    array(
                        'module' => 'SomeModule',
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                        'source' => 'some_table'
                    )
                )
            )
        );
    }
}
