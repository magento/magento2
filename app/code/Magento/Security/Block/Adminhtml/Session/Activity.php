<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Block\Adminhtml\Session;

use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Security\Model\ConfigInterface;

/**
 * Block Session Activity
 */
class Activity extends \Magento\Backend\Block\Template
{
    /**
     * @var ConfigInterface
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
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param ConfigInterface $securityConfig
     * @param \Magento\Security\Model\AdminSessionsManager $sessionsManager
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        ConfigInterface $securityConfig,
        \Magento\Security\Model\AdminSessionsManager $sessionsManager,
        RemoteAddress $remoteAddress
    ) {
        parent::__construct($context);
        $this->securityConfig = $securityConfig;
        $this->sessionsManager = $sessionsManager;
        $this->remoteAddress = $remoteAddress;
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
        return $this->remoteAddress->getRemoteAddress(false);
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
