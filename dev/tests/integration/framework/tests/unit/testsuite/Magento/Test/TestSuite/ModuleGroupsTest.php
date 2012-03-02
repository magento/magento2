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
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Test_TestSuite_ModuleGroupsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param bool $moduleGroupsOnly
     * @param array $enabledModules
     * @param array $groups
     * @param array $excludeGroups
     * @param array $expectedTestCases
     * @dataProvider runDataProvider
     */
    public function testRun($moduleGroupsOnly, $enabledModules, $groups, $excludeGroups, $expectedTestCases)
    {
        // Prepare suite
        $suite = $this->getMock(
            'Magento_Test_TestSuite_ModuleGroups',
            array('runTest'),
            array($moduleGroupsOnly)
        );

        // Stubs enabled modules call
        $stubHelper = $this->getMock(
            'Magento_Test_Helper_Config',
            array('getEnabledModules')
        );
        $stubHelper->expects($this->any())
            ->method('getEnabledModules')
            ->will($this->returnValue($enabledModules));
        $prevHelper = Magento_Test_Helper_Factory::setHelper('config', $stubHelper);

        // Callback that receives list of run tests
        $runTestCases = array();
        $func = function (PHPUnit_Framework_Test $test) use (&$runTestCases) {
            $runTestCases[] = get_class($test);
        };

        $suite->expects($this->any())
            ->method('runTest')
            ->will($this->returnCallback($func));

        $this->_fillTests($suite);
        $suite->run(null, false, $groups, $excludeGroups, false);

        // Restore old values
        Magento_Test_Helper_Factory::setHelper('config', $prevHelper);

        // Sort arrays to be sure, as order of tests is not important for us
        sort($expectedTestCases);
        sort($runTestCases);
        $this->assertEquals($expectedTestCases, $runTestCases);
    }

    /**
     * @return array
     */
    public function runDataProvider()
    {
        $result['enabled_modules_plus_nonmodule_tests'] = array(
            'moduleGroupsOnly' =>   false,
            'enabledModules' =>     array('Mage_Core', 'Author_Module'),
            'groups' =>             array(),
            'excludeGroups' =>      array(),
            'expectedTestCases' =>  array('Mg_Mage_Core', 'Mg_Author_Module', 'Mg_Integrity')
        );
        $result['enabled_modules_without_nonmodule_tests'] = array(
            'moduleGroupsOnly' =>   true,
            'enabledModules' =>     array('Mage_Core', 'Author_Module'),
            'groups' =>             array(),
            'excludeGroups' =>      array(),
            'expectedTestCases' =>  array('Mg_Mage_Core', 'Mg_Author_Module')
        );
        $result['groups_matter'] = array(
            'moduleGroupsOnly' =>   false,
            'enabledModules' =>     array('Mage_Core', 'Mage_Catalog', 'Author_Module'),
            'groups' =>             array('module:Mage_Catalog'),
            'excludeGroups' =>      array(),
            'expectedTestCases' =>  array('Mg_Mage_Catalog')
        );
        $result['groups_include_all_marked_tests'] = array(
            'moduleGroupsOnly' =>   false,
            'enabledModules' =>     array('Mage_Core', 'Mage_Eav'),
            'groups' =>             array('module:Mage_Core'),
            'excludeGroups' =>      array(),
            'expectedTestCases' =>  array('Mg_Mage_Core', 'Mg_Mage_Core_Mage_Eav')
        );
        $result['groups_not_pattern'] = array(
            'moduleGroupsOnly' =>   false,
            'enabledModules' =>     array('Mage_Core'),
            'groups' =>             array('module:Mage_C'),
            'excludeGroups' =>      array(),
            'expectedTestCases' =>  array()
        );
        $result['groups_wrong_pattern'] = array(
            'moduleGroupsOnly' =>   false,
            'enabledModules' =>     array('Mage_Core'),
            'groups' =>             array('#module:Mage_C.*#'),
            'excludeGroups' =>      array(),
            'expectedTestCases' =>  array()
        );
        $result['groups_pattern'] = array(
            'moduleGroupsOnly' =>   false,
            'enabledModules' =>     array('Mage_Core', 'Mage_Catalog', 'Mage_Eav', 'Author_Module'),
            'groups' =>             array('/module:Mage_C.*/'),
            'excludeGroups' =>      array(),
            'expectedTestCases' =>  array('Mg_Mage_Core', 'Mg_Mage_Catalog', 'Mg_Mage_Core_Mage_Eav')
        );
        $result['exclude_groups_matter'] = array(
            'moduleGroupsOnly' =>   false,
            'enabledModules' =>     array('Mage_Core', 'Mage_Catalog', 'Mage_Eav', 'Author_Module'),
            'groups' =>             array(),
            'excludeGroups' =>      array('module:Mage_Core'),
            'expectedTestCases' =>  array('Mg_Mage_Catalog', 'Mg_Author_Module', 'Mg_Integrity')
        );
        $result['exclude_groups_pattern'] = array(
            'moduleGroupsOnly' =>   false,
            'enabledModules' =>     array('Mage_Core', 'Mage_Catalog', 'Mage_Eav', 'Author_Module'),
            'groups' =>             array(),
            'excludeGroups' =>      array('/module:Mage_C.*/'),
            'expectedTestCases' =>  array('Mg_Author_Module', 'Mg_Integrity')
        );
        $result['groups_and_exclude_groups'] = array(
            'moduleGroupsOnly' =>   false,
            'enabledModules' =>     array('Mage_Core', 'Mage_Catalog', 'Mage_Eav', 'Author_Module'),
            'groups' =>             array('module:Mage_Core'),
            'excludeGroups' =>      array('module:Mage_Eav'),
            'expectedTestCases' =>  array('Mg_Mage_Core')
        );

        return $result;
    }

    /**
     * Adds fixture tests to the suite
     *
     * @param Magento_Test_TestSuite_ModuleGroups $suite
     * @return Magento_Test_TestSuite_ModuleGroupsTest
     */
    protected function _fillTests($suite)
    {
        $fileIteratorFactory = new File_Iterator_Factory();
        $fileIterator = $fileIteratorFactory->getFileIterator(
            array(__DIR__ . '/_files/ModuleGroups'),
            '.php'
        );

        // Manually make unique array of filenames, because we may get duplicate entries there
        $filenames = array();
        foreach ($fileIterator as $filename) {
            $filenames[] = (string) $filename;
        }
        $filenames = array_unique($filenames);

        // Compose test suite
        foreach ($filenames as $filename) {
            include_once $filename;

            $pathinfo = pathinfo($filename);
            $className = $pathinfo['filename'];

            $subSuite = new PHPUnit_Framework_TestSuite($className);
            $groups = $subSuite->getGroups();

            $obj = new $className();
            $suite->addTest($obj, $groups);
        }

        return $this;
    }
}
