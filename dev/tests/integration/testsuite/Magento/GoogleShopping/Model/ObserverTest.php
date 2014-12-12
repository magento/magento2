<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\GoogleShopping\Model;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/GoogleShopping/Model/_files/flag_expired.php
     */
    public function testCheckSynchronizationOperationsWithExpiredFlag()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\AdminNotification\Model\Inbox $inbox */
        $inbox = $objectManager->create('Magento\AdminNotification\Model\Inbox');
        $notice = $inbox->loadLatestNotice();
        $this->assertNotContains('Google Shopping', (string)$notice->getTitle());

        /** @var \Magento\GoogleShopping\Model\Observer $observer */
        $observer = $objectManager->get('Magento\GoogleShopping\Model\Observer');
        $dummyEventData = $this->getMock('\Magento\Framework\Event\Observer', [], [], '', false);
        $result = $observer->checkSynchronizationOperations($dummyEventData);

        $this->assertSame($observer, $result);

        $notice = $inbox->loadLatestNotice();
        $this->assertContains('Google Shopping', (string)$notice->getTitle());
    }
}
