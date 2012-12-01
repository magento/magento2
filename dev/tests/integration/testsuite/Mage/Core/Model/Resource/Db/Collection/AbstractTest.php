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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_Resource_Db_Collection_AbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    protected $_model = null;

    protected function setUp()
    {
        $resourceModel = Mage::getObjectManager()->get('Mage_Core_Model_Resource');
        $resource = $this->getMockForAbstractClass('Mage_Core_Model_Resource_Db_Abstract',
            array('resource' => $resourceModel), '', true, true, true, array('getMainTable', 'getIdFieldName')
        );

        $resource->expects($this->any())
            ->method('getMainTable')
            ->will($this->returnValue($resource->getTable('core_website')));
        $resource->expects($this->any())
            ->method('getIdFieldName')
            ->will($this->returnValue('website_id'));

        $this->_model = $this->getMockForAbstractClass(
            'Mage_Core_Model_Resource_Db_Collection_Abstract',
            array($resource)
        );
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    public function testGetAllIds()
    {
        $allIds = $this->_model->getAllIds();
        sort($allIds);
        $this->assertEquals(array('0', '1'), $allIds);
    }

    public function testGetAllIdsWithBind()
    {
        $this->_model->getSelect()->where('code = :code');
        $this->_model->addBindParam('code', 'admin');
        $this->assertEquals(array('0'), $this->_model->getAllIds());
    }
}
