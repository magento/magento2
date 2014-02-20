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
namespace Magento\Checkout\Block\Onepage;

class SuccessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    /**
     * @covers Magento\Checkout\Block\Onepage\Success::_prepareLastRecurringProfiles
     */
    public function testToHtmlPreparesRecurringProfiles()
    {
        $checkoutSessionArgs = $this->objectManager->getConstructArguments(
            'Magento\Checkout\Model\Session',
            array('storage' => new \Magento\Session\Storage('checkout'))
        );
        $checkoutSession = $this->getMock(
            'Magento\Checkout\Model\Session',
            ['getLastRecurringProfileIds'],
            $checkoutSessionArgs
        );
        $checkoutSession->expects($this->once())
            ->method('getLastRecurringProfileIds')
            ->will($this->returnValue([1, 2, 3]));
        $collection = $this->getMock(
            'Magento\RecurringProfile\Model\Resource\Profile\Collection',
            ['addFieldToFilter'],
            [],
            '',
            false
        );
        $collection->expects($this->once())->method('addFieldToFilter')
            ->with('profile_id', ['in' => [1, 2, 3]])->will($this->returnValue([]));
        $recurringProfileCollectionFactory = $this->getMock(
            'Magento\RecurringProfile\Model\Resource\Profile\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $recurringProfileCollectionFactory->expects($this->once())
            ->method('create')->will($this->returnValue($collection));

        /** @var \Magento\Checkout\Block\Onepage\Success $block */
        $block = $this->objectManager->getObject(
            'Magento\Checkout\Block\Onepage\Success',
            array(
                'checkoutSession' => $checkoutSession,
                'recurringProfileCollectionFactory' => $recurringProfileCollectionFactory,
            )
        );
        $this->assertEquals('', $block->toHtml());
    }

    public function testGetAdditionalInfoHtml()
    {
        /** @var \Magento\Checkout\Block\Onepage\Success $block */
        $block = $this->objectManager->getObject('Magento\Checkout\Block\Onepage\Success');
        $layout = $this->getMock('Magento\View\LayoutInterface', [], [], '', false);
        $layout->expects($this->once())
            ->method('renderElement')
            ->with('order.success.additional.info')
            ->will($this->returnValue('AdditionalInfoHtml'));
        $block->setLayout($layout);
        $this->assertEquals('AdditionalInfoHtml', $block->getAdditionalInfoHtml());
    }
}
