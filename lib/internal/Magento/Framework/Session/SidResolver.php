<?php
/**
 * SID resolver
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session;

/**
 * Class \Magento\Framework\Session\SidResolver
 *
 * @since 2.0.0
 */
class SidResolver implements SidResolverInterface
{
    /**
     * Config path for flag whether use SID on frontend
     */
    const XML_PATH_USE_FRONTEND_SID = 'web/session/use_frontend_sid';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\UrlInterface
     * @since 2.0.0
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\App\RequestInterface
     * @since 2.0.0
     */
    protected $request;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $sidNameMap;

    /**
     * Use session var instead of SID for session in URL
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_useSessionVar = false;

    /**
     * Use session in URL flag
     *
     * @var bool
     * @see \Magento\Framework\UrlInterface
     * @since 2.0.0
     */
    protected $_useSessionInUrl = true;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_scopeType;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $scopeType
     * @param array $sidNameMap
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request,
        $scopeType,
        array $sidNameMap = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        $this->sidNameMap = $sidNameMap;
        $this->_scopeType = $scopeType;
    }

    /**
     * @param SessionManagerInterface $session
     * @return string
     * @since 2.0.0
     */
    public function getSid(SessionManagerInterface $session)
    {
        $sidKey = null;
        $useSidOnFrontend = $this->scopeConfig->getValue(
            self::XML_PATH_USE_FRONTEND_SID,
            $this->_scopeType
        );
        if ($useSidOnFrontend && $this->request->getQuery(
            $this->getSessionIdQueryParam($session),
            false
        ) && $this->urlBuilder->isOwnOriginUrl()
        ) {
            $sidKey = $this->request->getQuery($this->getSessionIdQueryParam($session));
        }
        return $sidKey;
    }

    /**
     * Get session id query param
     *
     * @param SessionManagerInterface $session
     * @return string
     * @since 2.0.0
     */
    public function getSessionIdQueryParam(SessionManagerInterface $session)
    {
        $sessionName = $session->getName();
        if ($sessionName && isset($this->sidNameMap[$sessionName])) {
            return $this->sidNameMap[$sessionName];
        }
        return self::SESSION_ID_QUERY_PARAM;
    }

    /**
     * Set use session var instead of SID for URL
     *
     * @param bool $var
     * @return $this
     * @since 2.0.0
     */
    public function setUseSessionVar($var)
    {
        $this->_useSessionVar = (bool)$var;
        return $this;
    }

    /**
     * Retrieve use flag session var instead of SID for URL
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getUseSessionVar()
    {
        return $this->_useSessionVar;
    }

    /**
     * Set Use session in URL flag
     *
     * @param bool $flag
     * @return $this
     * @since 2.0.0
     */
    public function setUseSessionInUrl($flag = true)
    {
        $this->_useSessionInUrl = (bool)$flag;
        return $this;
    }

    /**
     * Retrieve use session in URL flag
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getUseSessionInUrl()
    {
        return $this->_useSessionInUrl;
    }
}
