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
namespace Magento\Framework\Module;

class DependencyManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Module\DependencyManager
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new \Magento\Framework\Module\DependencyManager();
    }

    /**
     * @param array $moduleConfig
     * @dataProvider checkModuleDependenciesDataProvider
     */
    public function testCheckModuleDependenciesDoesNotThrowExceptionIfAllDependenciesAreCorrect(array $moduleConfig)
    {
        $this->model->checkModuleDependencies($moduleConfig, array('Module_Three'));
    }

    /**
     * @param array $moduleConfig
     * @dataProvider checkModuleDependenciesDataProvider
     * @expectedException \Exception
     * @expectedExceptionMessage Module 'Module_One' depends on 'Module_Two' that is missing or not active.
     */
    public function testCheckModuleDependenciesNegativeModuleMissed(array $moduleConfig)
    {
        $moduleConfig['dependencies']['modules'][] = 'Module_Two';
        $this->model->checkModuleDependencies($moduleConfig, array('Module_Three'));
    }

    /**
     * @param array $moduleConfig
     * @dataProvider checkModuleDependenciesDataProvider
     * @expectedException \Exception
     * @expectedExceptionMessage Module 'Module_One' depends on 'custom_extension' PHP extension that is not loaded.
     */
    public function testCheckModuleDependenciesNegativeExtensionMissed(array $moduleConfig)
    {
        $moduleConfig['dependencies']['extensions']['strict'][] = array('name' => 'custom_extension');
        $this->model->checkModuleDependencies($moduleConfig);
    }

    /**
     * @param array $moduleConfig
     * @dataProvider checkModuleDependenciesDataProvider
     * @expectedException \Exception
     * @expectedExceptionMessage Module 'Module_One' depends on at least one of the following PHP extensions: ext1,ext2.
     */
    public function testCheckModuleDependenciesNegativeAlternativeExtensionMissed(array $moduleConfig)
    {
        $moduleConfig['dependencies']['extensions']['alternatives'][] = array(
            array('name' => 'ext1'),
            array('name' => 'ext2')
        );
        $this->model->checkModuleDependencies($moduleConfig);
    }

    /**
     * return array
     */
    public function checkModuleDependenciesDataProvider()
    {
        return array(
            array(
                'Module_One' => array(
                    'name' => 'Module_One',
                    'version' => '1.0.0.0',
                    'active' => true,
                    'dependencies' => array(
                        'modules' => array(),
                        'extensions' => array(
                            'strict' => array(
                                array(
                                    'name' => 'simplexml',
                                    'minVersion' => '0.0.0.1',
                                ),array(
                                    'name' => 'spl',
                                )
                            ),
                            'alternatives' => array(
                                array(
                                    array('name' => 'dom'),
                                    array('name' => 'hash')
                                )
                            )
                        )
                    )
                )
            )
        );
    }

    /**
     * @param array $modules
     * @dataProvider getExtendedModuleDependenciesDataProvider
     * @expectedException \Exception
     * @expectedExceptionMessage Module 'Module_Two' cannot depend on 'Module_One' since it creates circular dependency.
     */
    public function testGetExtendedModuleDependenciesNegativeCircularDependency(array $modules)
    {
        $modules['Module_Two']['dependencies']['modules'][] = 'Module_One';
        $this->model->getExtendedModuleDependencies('Module_One', $modules);
    }

    /**
     * @param array $modules
     * @dataProvider getExtendedModuleDependenciesDataProvider
     */
    public function testGetExtendedModuleDependenciesPositive(array $modules)
    {
        $this->assertEquals(array('Module_Two'), $this->model->getExtendedModuleDependencies('Module_One', $modules));
    }

    /**
     * return array
     */
    public function getExtendedModuleDependenciesDataProvider()
    {
        return array(
            array(
                'modules' => array(
                    'Module_One' => array(
                        'name' => 'Module_One',
                        'version' => '1.0.0.0',
                        'active' => true,
                        'dependencies' => array(
                            'modules' => array('Module_Two'),
                            'extensions' => array('strict' => array(), 'alternatives' => array())
                        )
                    ),
                    'Module_Two' => array(
                        'name' => 'Module_Two',
                        'version' => '1.0.0.0',
                        'active' => true,
                        'dependencies' => array(
                            'modules' => array(),
                            'extensions' => array('strict' => array(), 'alternatives' => array())
                        )
                    )
                )
            )
        );
    }
}
