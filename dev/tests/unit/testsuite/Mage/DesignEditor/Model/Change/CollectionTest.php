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
 * @package     Mage_DesignEditor
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_DesignEditor_Model_Change_CollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Collection model for testing
     *
     * @var Mage_DesignEditor_Model_Change_Collection
     */
    protected $_model;

    public function setUp()
    {
        parent::setUp();
        $this->_model = new Mage_DesignEditor_Model_Change_Collection;
    }

    /**
     * @covers Mage_DesignEditor_Model_Change_Collection::getItemClass
     */
    public function testGetItemClass()
    {
        $this->assertEquals('Mage_DesignEditor_Model_ChangeAbstract', $this->_model->getItemClass());
    }

    /**
     * Test toArray method
     *
     * @covers Mage_DesignEditor_Model_Change_Collection::toArray
     */
    public function testToArray()
    {
        $this->assertInternalType('array', $this->_model->toArray());
    }
}
