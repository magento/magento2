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
 * @package     Mage_Sales
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @group module:Mage_Sales
 */
class Mage_Sales_Block_Order_CommentsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Sales_Block_Order_Comments
     */
    protected $_block;

    public function setUp()
    {
        $this->_block = new Mage_Sales_Block_Order_Comments;
    }

    /**
     * @param mixed $commentedEntity
     * @param string $expectedClass
     * @dataProvider getCommentsDataProvider
     */
    public function testGetComments($commentedEntity, $expectedClass)
    {
        $this->_block->setEntity($commentedEntity);
        $comments = $this->_block->getComments();
        $this->assertInstanceOf($expectedClass, $comments);
    }

    /**
     * @return array
     */
    public function getCommentsDataProvider()
    {
        return array(
            array(
                new Mage_Sales_Model_Order_Invoice,
                'Mage_Sales_Model_Resource_Order_Invoice_Comment_Collection'
            ),
            array(
                new Mage_Sales_Model_Order_Creditmemo,
                'Mage_Sales_Model_Resource_Order_Creditmemo_Comment_Collection'
            ),
            array(
                new Mage_Sales_Model_Order_Shipment,
                'Mage_Sales_Model_Resource_Order_Shipment_Comment_Collection'
            )
        );
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testGetCommentsWrongEntityException()
    {
        $entity = new Mage_Catalog_Model_Product;
        $this->_block->setEntity($entity);
        $this->_block->getComments();
    }
}
