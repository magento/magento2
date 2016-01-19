<?php

namespace Magento\Security\Model\ResourceModel\AdminSessionInfo {

    /**
     * Class AdminSessionInfoTest
     * @package Magento\Security\Model\ResourceModel\
     */
    class AdminSessionInfoTest extends \PHPUnit_Framework_TestCase
    {
        protected $_objectManager;

        protected function setUp()
        {
            parent::setUp();
            $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        }

        protected function tearDown()
        {
            $session = $this->_objectManager->create('Magento\Security\Model\AdminSessionInfo');
            /** @var $session \Magento\Security\Model\AdminSessionInfo */
            $session->getResource()->getConnection()->delete(
                $session->getResource()->getMainTable()
            );
            $this->_objectManager = null;
        }

        /**
         * @magentoDataFixture Magento/Security/_files/adminsession.php
         */
        public function testDeleteSessionsOlderThen()
        {
            $session = $this->_objectManager->create('Magento\Security\Model\AdminSessionInfo');
            /** @var $session \Magento\Security\Model\AdminSessionInfo */
            $session->getResource()->deleteSessionsOlderThen(strtotime('2016-01-20 12:00:00'));
            $collection = $session->getResourceCollection()
                ->addFieldToFilter('main_table.updated_at', ['lt' => '2016-01-20 12:00:00'])
                ->load();
            $count = $collection->count();
            $this->assertEquals(0, $count);
        }

        /**
         * @magentoDataFixture Magento/Security/_files/adminsession.php
         */
        public function testupdateStatusByUserId()
        {
            $session = $this->_objectManager->create('Magento\Security\Model\AdminSessionInfo');
            /** @var $session \Magento\Security\Model\AdminSessionInfo */
            $session->getResource()->updateStatusByUserId(\Magento\Security\Model\AdminSessionInfo::LOGGED_OUT_BY_LOGIN, 1, [1], [1], '2016-01-19 12:00:00');
            $collection = $session->getResourceCollection()
                ->addFieldToFilter('main_table.user_id', 1)
                ->addFieldToFilter('main_table.status', \Magento\Security\Model\AdminSessionInfo::LOGGED_OUT_BY_LOGIN)
                ->load();
            $count = $collection->count();
            $this->assertGreaterThanOrEqual(1, $count);
        }
    }
}