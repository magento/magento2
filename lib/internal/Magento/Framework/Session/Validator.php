<?php
/**
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Session;

/**
 * Session Validator
 */
class Validator implements ValidatorInterface
{
    const VALIDATOR_KEY = '_session_validator_data';

    const VALIDATOR_HTTP_USER_AGENT_KEY = 'http_user_agent';

    const VALIDATOR_HTTP_X_FORWARDED_FOR_KEY = 'http_x_forwarded_for';

    const VALIDATOR_HTTP_VIA_KEY = 'http_via';

    const VALIDATOR_REMOTE_ADDR_KEY = 'remote_addr';

    const XML_PATH_USE_REMOTE_ADDR = 'web/session/use_remote_addr';

    const XML_PATH_USE_HTTP_VIA = 'web/session/use_http_via';

    const XML_PATH_USE_X_FORWARDED = 'web/session/use_http_x_forwarded_for';

    const XML_PATH_USE_USER_AGENT = 'web/session/use_http_user_agent';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $_remoteAddress;

    /**
     * @var array
     */
    protected $_skippedAgentList;

    /**
     * @var string
     */
    protected $_scopeType;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param string $scopeType
     * @param array $skippedUserAgentList
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        $scopeType,
        array $skippedUserAgentList = array()
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_remoteAddress = $remoteAddress;
        $this->_skippedAgentList = $skippedUserAgentList;
        $this->_scopeType = $scopeType;
    }

    /**
     * Validate session
     *
     * @param SessionManagerInterface $session
     * @return void
     * @throws Exception
     */
    public function validate(SessionManagerInterface $session)
    {
        if (!isset($_SESSION[self::VALIDATOR_KEY])) {
            $_SESSION[self::VALIDATOR_KEY] = $this->_getSessionEnvironment();
        } else {
            if (!$this->_validate()) {
                $session->destroy(array('clear_storage' => false));
                // throw core session exception
                throw new Exception('');
            }
        }
    }

    /**
     * Validate data
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _validate()
    {
        $sessionData = $_SESSION[self::VALIDATOR_KEY];
        $validatorData = $this->_getSessionEnvironment();

        if ($this->_scopeConfig->getValue(
            self::XML_PATH_USE_REMOTE_ADDR,
            $this->_scopeType
        ) && $sessionData[self::VALIDATOR_REMOTE_ADDR_KEY] != $validatorData[self::VALIDATOR_REMOTE_ADDR_KEY]
        ) {
            return false;
        }
        if ($this->_scopeConfig->getValue(
            self::XML_PATH_USE_HTTP_VIA,
            $this->_scopeType
        ) && $sessionData[self::VALIDATOR_HTTP_VIA_KEY] != $validatorData[self::VALIDATOR_HTTP_VIA_KEY]
        ) {
            return false;
        }

        $httpXForwardedKey = $sessionData[self::VALIDATOR_HTTP_X_FORWARDED_FOR_KEY];
        $validatorXForwarded = $validatorData[self::VALIDATOR_HTTP_X_FORWARDED_FOR_KEY];
        if ($this->_scopeConfig->getValue(
            self::XML_PATH_USE_X_FORWARDED,
            $this->_scopeType
        ) && $httpXForwardedKey != $validatorXForwarded
        ) {
            return false;
        }
        if ($this->_scopeConfig->getValue(
            self::XML_PATH_USE_USER_AGENT,
            $this->_scopeType
        ) && $sessionData[self::VALIDATOR_HTTP_USER_AGENT_KEY] != $validatorData[self::VALIDATOR_HTTP_USER_AGENT_KEY]
        ) {
            foreach ($this->_skippedAgentList as $agent) {
                if (preg_match('/' . $agent . '/iu', $validatorData[self::VALIDATOR_HTTP_USER_AGENT_KEY])) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    /**
     * Prepare session environment data for validation
     *
     * @return array
     */
    protected function _getSessionEnvironment()
    {
        $parts = array(
            self::VALIDATOR_REMOTE_ADDR_KEY => '',
            self::VALIDATOR_HTTP_VIA_KEY => '',
            self::VALIDATOR_HTTP_X_FORWARDED_FOR_KEY => '',
            self::VALIDATOR_HTTP_USER_AGENT_KEY => ''
        );

        // collect ip data
        if ($this->_remoteAddress->getRemoteAddress()) {
            $parts[self::VALIDATOR_REMOTE_ADDR_KEY] = $this->_remoteAddress->getRemoteAddress();
        }
        if (isset($_ENV['HTTP_VIA'])) {
            $parts[self::VALIDATOR_HTTP_VIA_KEY] = (string)$_ENV['HTTP_VIA'];
        }
        if (isset($_ENV['HTTP_X_FORWARDED_FOR'])) {
            $parts[self::VALIDATOR_HTTP_X_FORWARDED_FOR_KEY] = (string)$_ENV['HTTP_X_FORWARDED_FOR'];
        }

        // collect user agent data
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $parts[self::VALIDATOR_HTTP_USER_AGENT_KEY] = (string)$_SERVER['HTTP_USER_AGENT'];
        }

        return $parts;
    }
}
