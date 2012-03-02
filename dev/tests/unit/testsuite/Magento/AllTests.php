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
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Magento Library');
        $suite->addTestSuite('Magento_AutoloadTest');
        $suite->addTestSuite('Magento_CryptTest');
        $suite->addTestSuite('Magento_ProfilerTest');
        $suite->addTest(Magento_Config_AllTests::suite());
        $suite->addTest(Magento_Profiler_AllTests::suite());
        $suite->addTest(Magento_Convert_AllTests::suite());
        return $suite;
    }
}
