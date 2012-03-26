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

/**
 * @group module:Mage_Core
 */
class Mage_Core_Model_Layout_StructureTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Layout_Structure
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_Core_Model_Layout_Structure;
    }

    /**
     * If several parents with the same name exist
     */
    public function testGetParentName()
    {
        $parent = 'parent';
        $child1 = 'child1';
        $child2 = 'child2';
        $this->_model->insertElement('', $parent, 'container');
        $this->assertEmpty($this->_model->getParentName($parent));

        $this->_model->insertElement($parent, $child1, 'block');
        $parentName = $this->_model->getParentName($child1);
        $this->assertEquals($parent, $parentName);

        $this->_model->insertElement('', $parent, 'block');
        $this->assertEmpty($this->_model->getParentName($parent));
        $this->_model->insertElement($parent, $child2, 'block');
        $parentName = $this->_model->getParentName($child2);
        $this->assertEquals($parent, $parentName);
    }
}