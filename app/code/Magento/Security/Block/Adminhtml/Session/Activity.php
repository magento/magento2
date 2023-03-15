<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Block\Adminhtml\Session;

use DateTime;
use IntlDateFormatter;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Security\Model\AdminSessionsManager;
use Magento\Security\Model\ConfigInterface;
use Magento\Security\Model\ResourceModel\AdminSessionInfo\Collection as AdminSessionInfoCollection;
use Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory;

/**
 * Block Session Activity
 *
 * @api
 * @since 100.1.0
 */
class Activity extends Template
{
    /**
     * @var CollectionFactory
     * @since 100.1.0
     */
    protected $sessionsInfoCollection;

    /**
     * @param TemplateContext $context
     * @param ConfigInterface $securityConfig
     * @param AdminSessionsManager $sessionsManager
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(
        TemplateContext $context,
        protected readonly ConfigInterface $securityConfig,
        protected readonly AdminSessionsManager $sessionsManager,
        private readonly RemoteAddress $remoteAddress
    ) {
        parent::__construct($context);
    }

    /**
     * @return AdminSessionInfoCollection
     * @since 100.1.0
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
     * @since 100.1.0
     */
    public function areMultipleSessionsActive()
    {
        return count($this->getSessionInfoCollection()) > 1;
    }

    /**
     * @return string
     * @since 100.1.0
     */
    public function getRemoteIp()
    {
        return $this->remoteAddress->getRemoteAddress(false);
    }

    /**
     * Retrieve formatting datetime
     *
     * @param   string $time
     * @return  string
     * @since 100.1.0
     */
    public function formatDateTime($time)
    {
        $time = new DateTime($time);
        return $this->_localeDate->formatDateTime(
            $time,
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::MEDIUM
        );
    }
}
