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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogInventory\Model\Adminhtml\Stock;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Adminhtml\Stock\Item|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * setUp
     */
    protected function setUp()
    {
        $resourceMock = $this->getMock(
            'Magento\Framework\Model\Resource\AbstractResource',
            array('_construct', '_getReadAdapter', '_getWriteAdapter', 'getIdFieldName'),
            array(),
            '',
            false
        );
        $objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_model = $objectHelper->getObject(
            '\Magento\CatalogInventory\Model\Adminhtml\Stock\Item',
            array('resource' => $resourceMock)
        );
    }

    public function testGetCustomerGroupId()
    {
        $this->_model->setCustomerGroupId(null);
        $this->assertEquals(32000, $this->_model->getCustomerGroupId());
        $this->_model->setCustomerGroupId(2);
        $this->assertEquals(2, $this->_model->getCustomerGroupId());
    }

    public function testIsQtyCheckApplicable()
    {
        $this->assertTrue($this->_model->checkQty(1.0));
    }

    public function testCheckQuoteItemQty()
    {
        $this->_model->setData('manage_stock', 1);
        $this->_model->setData('is_in_stock', 1);
        $this->_model->setProductName('qwerty');
        $this->_model->setData('backorders', 3);
        $result = $this->_model->checkQuoteItemQty(1, 1);
        $this->assertEquals('We don\'t have as many "qwerty" as you requested.', $result->getMessage());
    }
}
