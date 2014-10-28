<?php
/**
 * Unit test for customer adminhtml model
 *
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

/**
 * Test class for \Magento\Customer\Model\Backend\Customer testing
 */
namespace Magento\Customer\Model\Backend;

class CustomerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Store\Model\StoreManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $_storeManager;

    /** @var \Magento\Customer\Model\Backend\Customer */
    protected $_model;

    /**
     * Create model
     */
    protected function setUp()
    {
        $this->_storeManager = $this->getMock('Magento\Store\Model\StoreManager', array(), array(), '', false);
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $helper->getObject(
            'Magento\Customer\Model\Backend\Customer',
            array('storeManager' => $this->_storeManager)
        );
    }

    /**
     * @dataProvider getStoreDataProvider
     * @param $websiteId
     * @param $websiteStoreId
     * @param $storeId
     * @param $result
     */
    public function testGetStoreId($websiteId, $websiteStoreId, $storeId, $result)
    {
        if ($websiteId * 1) {
            $this->_model->setWebsiteId($websiteId);
            $website = new \Magento\Framework\Object(array('store_ids' => array($websiteStoreId)));
            $this->_storeManager->expects($this->once())->method('getWebsite')->will($this->returnValue($website));
        } else {
            $this->_model->setStoreId($storeId);
            $this->_storeManager->expects($this->never())->method('getWebsite');
        }
        $this->assertEquals($result, $this->_model->getStoreId());
    }

    /**
     * Data provider for testGetStoreId
     * @return array
     */
    public function getStoreDataProvider()
    {
        return array(array(1, 10, 5, 10), array(0, 10, 5, 5));
    }
}
