<?php

namespace Magento\Security\Model\AdminSessionsManager {

    /**
     * @magentoAppArea adminhtml
     */
    class AdminSessionsManagerTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * @var \Magento\Backend\Model\Auth
         */
        protected $_auth;

        /**
         * @var \Magento\Backend\Model\Auth\Session
         */
        protected $_modelSession;

        /**
         * @var \Magento\Security\Model\AdminSessionInfo
         */
        protected $_modelSessionInfo;

        protected function setUp()
        {
            parent::setUp();
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Framework\Config\ScopeInterface'
            )->setCurrentScope(
                \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
            );
            $this->_auth = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\Backend\Model\Auth'
            );
            $this->_modelSession = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\Backend\Model\Auth\Session'
            );
            $this->_modelSessionInfo = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\Security\Model\AdminSessionInfo'
            );

            $this->_auth->setAuthStorage($this->_modelSession);
        }

        protected function tearDown()
        {
            $this->_model = null;
            $this->_modelSessionsManager  = null;
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Framework\Config\ScopeInterface'
            )->setCurrentScope(
                null
            );
        }

        /**
         * Checks is the admin session is created in database
         */
        public function testIsAdminSessionIsCreated()
        {
            $this->_auth->login(
                \Magento\TestFramework\Bootstrap::ADMIN_NAME,
                \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
            );
            $sessionId = $this->_modelSession->getSessionId();
            $this->_modelSessionInfo->load($sessionId, 'session_id');
            $this->assertGreaterThanOrEqual(1, (int)$this->_modelSessionInfo->getId());
            $this->_auth->logout();
        }

        /**
         *
         */
        public function testIsAdminSessionIsUpdating() {
            $this->_auth->login(
                \Magento\TestFramework\Bootstrap::ADMIN_NAME,
                \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
            );
            $sessionId = $this->_modelSession->getSessionId();
            $this->_modelSessionInfo->load($sessionId, 'session_id');
            $this->_modelSession->setUpdatedAt(time() + 200);

            $this->_modelSession->prolong();

            $this->assertGreaterThan(
                strtotime($this->_modelSessionInfo->getCreatedAt()),
                strtotime($this->_modelSessionInfo->getUpdatedAt())
            );
            $this->_auth->logout();
        }

    }

}