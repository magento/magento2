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

class PhpRuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PhpRule
     */
    protected $model;

    protected function setUp()
    {
        $mapRoutes = array('someModule' => array('Magento\SomeModule'), 'anotherModule' => array('Magento\OneModule'));
        $mapLayoutBlocks = array('area' => array('block.name' => array('Magento\SomeModule' => 'Magento\SomeModule')));
        $this->model = new PhpRule($mapRoutes, $mapLayoutBlocks);
    }

    public function testNonPhpGetDependencyInfo()
    {
        $content = 'any content';
        $this->assertEmpty($this->model->getDependencyInfo('any', 'not php', 'any', $content));
    }

    /**
     * @param string $module
     * @param string $content
     * @param array $expected
     * @dataProvider getDependencyInfoDataProvider
     */
    public function testGetDependencyInfo($module, $content, array $expected)
    {
        $this->assertEquals($expected, $this->model->getDependencyInfo($module, 'php', 'any', $content));
    }

    public function getDependencyInfoDataProvider()
    {
        return array(
            array('Magento\SomeModule', 'something extends \Magento\SomeModule\Any\ClassName {', array()), //1
            array(
                'Magento\AnotherModule',
                'something extends \Magento\SomeModule\Any\ClassName {',
                array(
                    array(
                        'module' => 'Magento\SomeModule',
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                        'source' => 'Magento\SomeModule\Any\ClassName'
                    )
                )
            ), // 2
            array(
                'Magento\SomeModule',
                '$this->getViewFileUrl("Magento_SomeModule::js/order-by-sku-failure.js")',
                array()
            ), // 3
            array(
                'Magento\AnotherModule',
                '$this->getViewFileUrl("Magento_SomeModule::js/order-by-sku-failure.js")',
                array(
                    array(
                        'module' => 'Magento\SomeModule',
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                        'source' => 'Magento_SomeModule'
                    )
                )
            ), //4
            array('Magento\SomeModule', '$this->helper("Magento\SomeModule\Any\ClassName")', array()), //5
            array(
                'Magento\AnotherModule',
                '$this->helper("Magento\SomeModule\Any\ClassName")',
                array(
                    array(
                        'module' => 'Magento\SomeModule',
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                        'source' => 'Magento\SomeModule\Any\ClassName'
                    )
                )
            ), //6
            array('Magento\SomeModule', '$this->getUrl("someModule")', array()), // 7
            array(
                'Magento\AnotherModule',
                '$this->getUrl("anotherModule")',
                array(
                    array(
                        'module' => 'Magento\OneModule',
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                        'source' => 'getUrl("anotherModule"'
                    )
                )
            ), //8
            array('Magento\SomeModule', '$this->getLayout()->getBlock(\'block.name\');', array()), // 9
            array(
                'Magento\AnotherModule',
                '$this->getLayout()->getBlock(\'block.name\');',
                array(
                    array(
                        'module' => 'Magento\SomeModule',
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                        'source' => 'getBlock(\'block.name\')'
                    )
                )
            ) // 10
        );
    }

    /**
     * @param string $module
     * @param string $content
     * @param array $expected
     * @dataProvider getDefaultModelDependencyDataProvider
     */
    public function testGetDefaultModelDependency($module, $content, array $expected)
    {
        $mapLayoutBlocks = array(
            'default' => array(
                'block.name' => array(
                    'Magento\SomeModule' => 'Magento\SomeModule',
                    'Magento\Install' => 'Magento\Install'
                )
            )
        );
        $this->model = new PhpRule(array(), $mapLayoutBlocks);
        $this->assertEquals($expected, $this->model->getDependencyInfo($module, 'template', 'any', $content));
    }

    public function getDefaultModelDependencyDataProvider()
    {
        return array(
            array(
                'Magento\AnotherModule',
                '$this->getLayout()->getBlock(\'block.name\');',
                array(
                    array(
                        'module' => 'Magento\Install',
                        'type' => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                        'source' => 'getBlock(\'block.name\')'
                    )
                )
            )
        );
    }
}
