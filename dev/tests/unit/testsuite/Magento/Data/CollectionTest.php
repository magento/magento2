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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Data;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Data\Collection
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = new \Magento\Data\Collection(
            $this->getMock('Magento\Core\Model\EntityFactory', array(), array(), '', false)
        );
    }

    public function testRemoveAllItems()
    {
        $this->_model->addItem(new \Magento\Object());
        $this->_model->addItem(new \Magento\Object());
        $this->assertCount(2, $this->_model->getItems());
        $this->_model->removeAllItems();
        $this->assertEmpty($this->_model->getItems());
    }

    /**
     * @dataProvider setItemObjectClassDataProvider
     */
    public function testSetItemObjectClass($class)
    {
        $this->_model->setItemObjectClass($class);
        $this->assertAttributeSame($class, '_itemObjectClass', $this->_model);
    }

    /**
     * @return array
     */
    public function setItemObjectClassDataProvider()
    {
        return array(
            array('Magento\Core\Model\Url'),
            array('Magento\Object'),
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Incorrect_ClassName does not extend \Magento\Object
     */
    public function testSetItemObjectClassException()
    {
        $this->_model->setItemObjectClass('Incorrect_ClassName');
    }
}
