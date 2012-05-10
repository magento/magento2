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
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_Design_Source_DesignTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Design_Source_Design
     */
    protected $_model = null;

    public static function setUpBeforeClass()
    {
        Mage::getConfig()->getOptions()->setDesignDir(__DIR__ . '/_files/design');
    }

    protected function setUp()
    {
        $this->_model = new Mage_Core_Model_Design_Source_Design;
    }

    public function testGetAllOptionsSorting()
    {
        $fixture = array(
            array(
                'label' => 'Default / Default',
                'value' => array(
                    array(
                        'label' => 'default (incompatible version)',
                        'value' => 'default/default/default',
                    ),
                ),
            ),
            array(
                'label' => 'Default / Theme G',
                'value' => array(
                    array(
                        'label' => 'default (incompatible version)',
                        'value' => 'default/g/default',
                    ),
                ),
            ),
            array(
                'label' => 'Package A / Theme D',
                'value' => array(
                    array(
                        'label' => 'y (incompatible version)',
                        'value' => 'a/d/y',
                    ),
                ),
            ),
            array(
                'label' => 'Package B / Theme E',
                'value' => array(
                    array(
                        'label' => 'x (incompatible version)',
                        'value' => 'b/e/x',
                    ),
                ),
            ),
        );
        $this->assertSame($fixture, $this->_model->getAllOptions(false));
    }

    public function testGetThemeOptionsSorting()
    {
        $fixture = array(
            array(
                'label' => 'Default',
                'value' => array(
                    array(
                        'label' => 'Default (incompatible version)',
                        'value' => 'default/default',
                    ),
                    array(
                        'label' => 'Theme G (incompatible version)',
                        'value' => 'default/g',
                    ),
                ),
            ),
            array(
                'label' => 'Package A',
                'value' => array(
                    array(
                        'label' => 'Theme D (incompatible version)',
                        'value' => 'a/d',
                    ),
                ),
            ),
            array(
                'label' => 'Package B',
                'value' => array(
                    array(
                        'label' => 'Theme E (incompatible version)',
                        'value' => 'b/e',
                    ),
                ),
            ),
        );
        $this->assertSame($fixture, $this->_model->getThemeOptions());
    }

    public function testGetOptions()
    {
        $this->assertSame($this->_model->getAllOptions(false), $this->_model->getOptions());
    }
}
