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
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Helper_TranslateTest extends PHPUnit_Framework_TestCase
{
    /**
     * Template helper mock
     *
     * @var Mage_Core_Helper_Translate
     */
    protected $_helper;

    protected function setUp()
    {
        parent::setUp();
        $this->_helper = new Mage_Core_Helper_Translate();
    }

    /**
     * @dataProvider testComposeLocaleHierarchyDataProvider
     */
    public function testComposeLocaleHierarchy($localeConfig, $localeHierarchy)
    {
        $this->assertEquals($localeHierarchy, $this->_helper->composeLocaleHierarchy($localeConfig));
    }

    public function testComposeLocaleHierarchyDataProvider()
    {
        return array(
            array(
                array(
                    'en_US' => 'en_UK',
                    'en_UK' => 'pt_BR',
                ),
                array(
                    'en_US' => array('pt_BR', 'en_UK'),
                    'en_UK' => array('pt_BR'),
                )
            ),
            array(
                array(
                    'en_US' => 'en_UK',
                    'en_UK' => 'en_US',
                ),
                array(
                    'en_US' => array('en_UK'),
                    'en_UK' => array('en_US'),
                )
            ),
            array(
                array(
                    'en_US' => '',
                    'en_UK' => 'wrong_locale'
                ),
                array(
                    'en_US' => array(''),
                    'en_UK' => array('wrong_locale')
                )
            ),
            array(
                array(),
                array()
            ),
        );
    }
}
