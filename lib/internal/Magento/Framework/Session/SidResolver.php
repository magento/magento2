<?php
/**
 * SID resolver
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session;

use Magento\Framework\App\State;

/**
 * Resolves SID by processing request parameters.
 *
 * @deprecated 102.0.2 SIDs in URLs are no longer used
 */
class SidResolver implements SidResolverInterface
{
    /**
     * Config path for flag whether use SID on frontend
     */
    const XML_PATH_USE_FRONTEND_SID = 'web/session/use_frontend_sid';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\UrlInterface
     * @deprecated 102.0.5 Not used anymore.
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\App\RequestInterface
     * @deprecated 102.0.5 Not used anymore.
     */
    protected $request;

    /**
     * @var array
     */
    protected $sidNameMap;

    /**
     * Use session var instead of SID for session in URL
     *
     * @var bool
     */
    protected $_useSessionVar = false;

    /**
     * Use session in URL flag
     *
     * @var bool|null
     * @see \Magento\Framework\UrlInterface
     */
    protected $_useSessionInUrl = false;

    /**
     * @var string
     */
    protected $_scopeType;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $scopeType
     * @param array $sidNameMap
     * @param State|null $appState
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request,
        $scopeType,
        array $sidNameMap = [],
        ?State $appState = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        $this->sidNameMap = $sidNameMap;
        $this->_scopeType = $scopeType;
    }

    /**
     * @inheritDoc
     */
    public function getSid(SessionManagerInterface $session)
    {
        trigger_error('Session ID is not used as URL parameter anymore.', E_USER_DEPRECATED);

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getSessionIdQueryParam(SessionManagerInterface $session)
    {
        trigger_error('Session ID is not used as URL parameter anymore.', E_USER_DEPRECATED);
        $sessionName = $session->getName();
        if ($sessionName && isset($this->sidNameMap[$sessionName])) {
            return $this->sidNameMap[$sessionName];
        }
        return self::SESSION_ID_QUERY_PARAM;
    }

    /**
     * @inheritDoc
     */
    public function setUseSessionVar($var)
    {
        trigger_error('Session ID is not used as URL parameter anymore.', E_USER_DEPRECATED);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUseSessionVar()
    {
        trigger_error('Session ID is not used as URL parameter anymore.', E_USER_DEPRECATED);

        return false;
    }

    /**
     * @inheritDoc
     */
    public function setUseSessionInUrl($flag = true)
    {
        trigger_error('Session ID is not used as URL parameter anymore.', E_USER_DEPRECATED);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUseSessionInUrl()
    {
        trigger_error('Session ID is not used as URL parameter anymore.', E_USER_DEPRECATED);

        return false;
    }
}
