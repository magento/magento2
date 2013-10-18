<?php
/**
 * Legacy class usages excluded from the ClassesTest->testClassReferences for PSR-X standards
 *
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
 * @category    tests
 * @package     static
 * @subpackage  Integrity
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

return array(
    'PEAR_PackageFile',
    'Simple_Xml',
    'PersistentCustomerSegmentation',
    'SolrClient',
    'SolrQuery',
    'RegionUpdater', // JavaScript usage
    'Fixture_Module', // JavaScript usage
    'TheCompoundNamespace_TheCompoundModule',
    'Some_Module',
    'Not_Existed_Class',
    'CustomSelect',

    // found in /dev/tests/unit/testsuite/Magento/Test/Tools/Di/_files/app/code/Magento/SomeModule/Model/Test.php
    '\Magento\SomeModule\Model\Element\Proxy',

    'Map_Module',
    'Module_One',
    'Module_Two',
    'Module_Name',
    'Test',
    'Local_Module',

    // found in /dev/tests/unit/testsuite/Magento/Webapi/Controller/Soap/HandlerTest.php
    '\Magento\Webapi\Controller\Soap\Security',

    'Mage',
    'Pear_Package_Parser_v2',
    'PEAR_Config',
    'PEAR_DependencyDB',
    'PEAR_Frontend',
    'PEAR_Command',
    'Zend',
    'PEAR_PackageFileManager2',
    'PEAR',
);
