<?php
/**
 * SID resolver
 *
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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Session;

use Magento\Session\SessionManagerInterface;
use Magento\Session\SidResolverInterface;

class SidResolver implements SidResolverInterface
{
    /**
     * Config path for flag whether use SID on frontend
     */
    const XML_PATH_USE_FRONTEND_SID = 'web/session/use_frontend_sid';

    /**
     * @var \Magento\Core\Model\Store\ConfigInterface
     */
    protected $coreStoreConfig;

    /**
     * @var \Magento\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\App\RequestInterface
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
     * @var bool
     * @see \Magento\UrlInterface
     */
    protected $_useSessionInUrl = true;

    /**
     * @param \Magento\Core\Model\Store\ConfigInterface $coreStoreConfig
     * @param \Magento\UrlInterface $urlBuilder
     * @param \Magento\App\RequestInterface $request
     * @param array $sidNameMap
     */
    public function __construct(
        \Magento\Core\Model\Store\ConfigInterface $coreStoreConfig,
        \Magento\UrlInterface $urlBuilder,
        \Magento\App\RequestInterface $request,
        array $sidNameMap = array()
    ) {
        $this->coreStoreConfig = $coreStoreConfig;
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        $this->sidNameMap = $sidNameMap;
    }

    /**
     * @param SessionManagerInterface $session
     * @return string
     */
    public function getSid(SessionManagerInterface $session)
    {
        $sidKey = null;
        if ($this->coreStoreConfig->getConfig(self::XML_PATH_USE_FRONTEND_SID)
            && $this->request->getQuery($this->getSessionIdQueryParam($session), false)
            && $this->urlBuilder->isOwnOriginUrl()
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
     */
    public function getUseSessionInUrl()
    {
        return $this->_useSessionInUrl;
    }
}
