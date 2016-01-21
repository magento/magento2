<?php
namespace Magento\Security\Model\ResourceModel\PasswordResetRequestEvent;


/**
 * Class PasswordResetRequestEventTest
 * @package Magento\Security\Model\ResourceModel\PasswordResetRequestEvent
 */
class PasswordResetRequestEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent
     */
    protected $_resourceModel;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected function setUp()
    {
        parent::setUp();
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_resourceModel = $this->_objectManager
            ->create('Magento\Security\Model\PasswordResetRequestEvent')
            ->getResource();
    }

    protected function tearDown()
    {
        $this->_objectManager = null;
        $this->_resourceModel = null;
        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Security/_files/password_reset_request_events.php
     */
    public function testDeleteRecordsOlderThen()
    {
        $passwordResetRequestEvent = $this->_objectManager->create('Magento\Security\Model\PasswordResetRequestEvent');
        /** @var \Magento\Security\Model\PasswordResetRequestEvent $passwordResetRequestEvent */
        $countBefore = $passwordResetRequestEvent->getCollection()->count();
        $this->_resourceModel->deleteRecordsOlderThen(strtotime('2016-01-20 12:00:00'));
        $countAfter = $passwordResetRequestEvent->getCollection()->count();
        $this->assertLessThan($countBefore, $countAfter);
    }
}