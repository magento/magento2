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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_Theme_LabelTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Theme_Label
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = Mage::getModel('Mage_Core_Model_Theme_Label');
    }

    /**
     * @covers Mage_Core_Model_Theme_Label::getLabelsCollection
     */
    public function testGetLabelsCollection()
    {
        /** @var $expectedCollection Mage_Core_Model_Resource_Theme_Collection */
        $expectedCollection = Mage::getModel('Mage_Core_Model_Resource_Theme_Collection');
        $expectedCollection->addAreaFilter(Mage_Core_Model_App_Area::AREA_FRONTEND)
            ->filterVisibleThemes();

        $expectedItemsCount = count($expectedCollection);

        $labelsCollection = $this->_model->getLabelsCollection();
        $this->assertEquals($expectedItemsCount, count($labelsCollection));

        $labelsCollection = $this->_model->getLabelsCollection('-- Please Select --');
        $this->assertEquals(++$expectedItemsCount, count($labelsCollection));
    }
}
