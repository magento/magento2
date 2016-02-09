<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Block\Adminhtml\Session;

/**
 * Block Session Activity
 */
class Activity extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Security\Helper\SecurityConfig
     */
    protected $securityConfig;

    /**
     * @var \Magento\Security\Model\AdminSessionsManager
     */
    protected $sessionsManager;

    /**
     * @var \Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory
     */
    protected $sessionsInfoCollection;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Security\Helper\SecurityConfig $securityConfig
     * @param \Magento\Security\Model\AdminSessionsManager $sessionsManager
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Security\Helper\SecurityConfig $securityConfig,
        \Magento\Security\Model\AdminSessionsManager $sessionsManager
    ) {
        parent::__construct($context);
        $this->securityConfig = $securityConfig;
        $this->sessionsManager = $sessionsManager;
    }

    /**
     * @return \Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection
     */
    public function getSessionInfoCollection()
    {
        if (null === $this->sessionsInfoCollection) {
            $this->sessionsInfoCollection = $this->sessionsManager->getSessionsForCurrentUser();
        }
        return $this->sessionsInfoCollection;
    }

    /**
     * @return bool
     */
    public function areMultipleSessionsActive()
    {
        return count($this->getSessionInfoCollection()) > 1;
    }

    /**
     * @return string
     */
    public function getRemoteIp()
    {
        return $this->securityConfig->getRemoteIp(false);
    }

    /**
     * Retrieve formatting datatime
     *
     * @param   string $time
     * @return  string
     */
    public function formatDateTime($time)
    {
        $time = new \DateTime($time);
        return $this->_localeDate->formatDateTime(
            $time,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::MEDIUM
        );
    }
}
