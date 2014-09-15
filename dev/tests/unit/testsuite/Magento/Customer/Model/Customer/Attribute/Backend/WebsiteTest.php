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

namespace Magento\Customer\Model\Customer\Attribute\Backend;

class WebsiteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Website
     */
    protected $testable;

    /**
     * @var \Magento\Framework\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    public function setUp()
    {
        $logger = $this->getMockBuilder('Magento\Framework\Logger')->disableOriginalConstructor()->getMock();
        /** @var \Magento\Framework\Logger $logger */
        $storeManager = $this->storeManager = $this->getMockBuilder('Magento\Framework\StoreManagerInterface')
            ->getMock();
        /** @var \Magento\Framework\StoreManagerInterface $storeManager */
        $this->testable = new Website($logger, $storeManager);
    }

    public function testBeforeSaveWithId()
    {
        $object = $this->getMockBuilder('Magento\Framework\Object')
            ->disableOriginalConstructor()
            ->setMethods(array('getId'))
            ->getMock();

        $object->expects($this->once())->method('getId')->will($this->returnValue(1));
        /** @var \Magento\Framework\Object $object */

        $this->assertInstanceOf(
            'Magento\Customer\Model\Customer\Attribute\Backend\Website',
            $this->testable->beforeSave($object)
        );
    }

    public function testBeforeSave()
    {
        $websiteId = 1;
        $object = $this->getMockBuilder('Magento\Framework\Object')
            ->disableOriginalConstructor()
            ->setMethods(array('hasData', 'setData'))
            ->getMock();

        $store = $this->getMockBuilder('Magento\Framework\Object')->setMethods(array('getWebsiteId'))->getMock();
        $store->expects($this->once())->method('getWebsiteId')->will($this->returnValue($websiteId));

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($store));

        $object->expects($this->once())->method('hasData')->with('website_id')->will($this->returnValue(false));
        $object->expects($this->once())
            ->method('setData')
            ->with($this->logicalOr('website_id', $websiteId))
            ->will($this->returnSelf());
        /** @var \Magento\Framework\Object $object */

        $this->assertInstanceOf(
            'Magento\Customer\Model\Customer\Attribute\Backend\Website',
            $this->testable->beforeSave($object)
        );
    }
}
