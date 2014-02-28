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
namespace Magento\RecurringProfile\Block\Profile\View;

/**
 * Test class for \Magento\RecurringProfile\Block\Profile\View\Item
 */
class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\RecurringProfile\Block\Profile\View\Item
     */
    protected $_block;

    /**
     * @var \Magento\RecurringProfile\Model\Profile
     */
    protected $_profile;

    public function testPrepareLayout()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_profile = $this->getMockBuilder('Magento\RecurringProfile\Model\Profile')
            ->disableOriginalConstructor()
            ->setMethods(array('setStore', 'getFieldLabel', '__wakeup'))
            ->getMock();
        $this->_profile->expects($this->once())->method('setStore')->will($this->returnValue($this->_profile));

        $registry = $this->getMockBuilder('Magento\Registry')
            ->disableOriginalConstructor()
            ->setMethods(array('registry'))
            ->getMock();
        $registry->expects($this->once())
            ->method('registry')
            ->with('current_recurring_profile')
            ->will($this->returnValue($this->_profile));

        $store = $this->getMockBuilder('Magento\Core\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $storeManager = $this->getMockBuilder('Magento\Core\Model\StoreManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getStore'))
            ->getMock();
        $storeManager->expects($this->once())->method('getStore')
            ->will($this->returnValue($store));

        $this->_block = $objectManager->getObject(
            'Magento\RecurringProfile\Block\Profile\View\Item',
            array(
                'registry' => $registry,
                'storeManager' => $storeManager,
            )
        );

        $layout = $this->getMockBuilder('Magento\Core\Model\Layout')
            ->disableOriginalConstructor()
            ->setMethods(array('helper'))
            ->getMock();

        $this->_block->setLayout($layout);
    }
}
