<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Model;

/**
 * Implementation of \Zend_Captcha
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class DefaultModel extends \Zend_Captcha_Image implements \Magento\Captcha\Model\ModelInterface
{
    /**
     * Key in session for captcha code
     */
    const SESSION_WORD = 'word';

    /**
     * Min captcha lengths default value
     */
    const DEFAULT_WORD_LENGTH_FROM = 3;

    /**
     * Max captcha lengths default value
     */
    const DEFAULT_WORD_LENGTH_TO = 5;

    /**
     * @var \Magento\Captcha\Helper\Data
     */
    protected $_captchaData;

    /**
     * Captcha expire time
     * @var int
     */
    protected $_expiration;

    /**
     * Override default value to prevent a captcha cut off
     * @var int
     * @see \Zend_Captcha_Image::$_fsize
     */
    protected $_fsize = 22;

    /**
     * Captcha form id
     * @var string
     */
    protected $_formId;

    /**
     * @var \Magento\Captcha\Model\Resource\LogFactory
     */
    protected $_resLogFactory;

    /**
     * Overrides parent parameter as session comes in constructor.
     *
     * @var bool
     */
    protected $_keepSession = true;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_session;

    /**
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Captcha\Helper\Data $captchaData
     * @param \Magento\Captcha\Model\Resource\LogFactory $resLogFactory
     * @param string $formId
     */
    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Captcha\Helper\Data $captchaData,
        \Magento\Captcha\Model\Resource\LogFactory $resLogFactory,
        $formId
    ) {
        $this->_session = $session;
        $this->_captchaData = $captchaData;
        $this->_resLogFactory = $resLogFactory;
        $this->_formId = $formId;
    }

    /**
     * Returns key with respect of current form ID
     *
     * @param string $key
     * @return string
     */
    protected function _getFormIdKey($key)
    {
        return $this->_formId . '_' . $key;
    }

    /**
     * Get Block Name
     *
     * @return string
     */
    public function getBlockName()
    {
        return 'Magento\Captcha\Block\Captcha\DefaultCaptcha';
    }

    /**
     * Whether captcha is required to be inserted to this form
     *
     * @param null|string $login
     * @return bool
     */
    public function isRequired($login = null)
    {
        if ($this->_isUserAuth() && !$this->isShownToLoggedInUser() || !$this->_isEnabled() || !in_array(
            $this->_formId,
            $this->_getTargetForms()
        )
        ) {
            return false;
        }

        return $this->_isShowAlways() || $this->_isOverLimitAttempts(
            $login
        ) || $this->_session->getData(
            $this->_getFormIdKey('show_captcha')
        );
    }

    /**
     * Check if CAPTCHA has to be shown to logged in user on this form
     *
     * @return bool
     */
    public function isShownToLoggedInUser()
    {
        $forms = (array)$this->_captchaData->getConfig('shown_to_logged_in_user');
        foreach ($forms as $formId => $isShownToLoggedIn) {
            if ($isShownToLoggedIn && $this->_formId == $formId) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check is over limit attempts
     *
     * @param string $login
     * @return bool
     */
    protected function _isOverLimitAttempts($login)
    {
        return $this->_isOverLimitIpAttempt() || $this->_isOverLimitLoginAttempts($login);
    }

    /**
     * Returns number of allowed attempts for same login
     *
     * @return int
     */
    protected function _getAllowedAttemptsForSameLogin()
    {
        return (int)$this->_captchaData->getConfig('failed_attempts_login');
    }

    /**
     * Returns number of allowed attempts from same IP
     *
     * @return int
     */
    protected function _getAllowedAttemptsFromSameIp()
    {
        return (int)$this->_captchaData->getConfig('failed_attempts_ip');
    }

    /**
     * Check is overlimit saved attempts from one ip
     *
     * @return bool
     */
    protected function _isOverLimitIpAttempt()
    {
        $countAttemptsByIp = $this->_getResourceModel()->countAttemptsByRemoteAddress();
        return $countAttemptsByIp >= $this->_getAllowedAttemptsFromSameIp();
    }

    /**
     * Is Over Limit Login Attempts
     *
     * @param string $login
     * @return bool
     */
    protected function _isOverLimitLoginAttempts($login)
    {
        if ($login != false) {
            $countAttemptsByLogin = $this->_getResourceModel()->countAttemptsByUserLogin($login);
            return $countAttemptsByLogin >= $this->_getAllowedAttemptsForSameLogin();
        }
        return false;
    }

    /**
     * Check is user auth
     *
     * @return bool
     */
    protected function _isUserAuth()
    {
        return $this->_session->isLoggedIn();
    }

    /**
     * Whether to respect case while checking the answer
     *
     * @return bool
     */
    public function isCaseSensitive()
    {
        return (string)$this->_captchaData->getConfig('case_sensitive');
    }

    /**
     * Get font to use when generating captcha
     *
     * @return string
     */
    public function getFont()
    {
        $font = (string)$this->_captchaData->getConfig('font');
        $fonts = $this->_captchaData->getFonts();

        if (isset($fonts[$font])) {
            $fontPath = $fonts[$font]['path'];
        } else {
            $fontData = array_shift($fonts);
            $fontPath = $fontData['path'];
        }

        return $fontPath;
    }

    /**
     * After this time isCorrect() is going to return FALSE even if word was guessed correctly
     *
     * @return int
     */
    public function getExpiration()
    {
        if (!$this->_expiration) {
            /**
             * as "timeout" configuration parameter specifies timeout in minutes - we multiply it on 60 to set
             * expiration in seconds
             */
            $this->_expiration = (int)$this->_captchaData->getConfig('timeout') * 60;
        }
        return $this->_expiration;
    }

    /**
     * Get timeout for session token
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->getExpiration();
    }

    /**
     * Get captcha image directory
     *
     * @return string
     */
    public function getImgDir()
    {
        return $this->_captchaData->getImgDir();
    }

    /**
     * Get captcha image base URL
     *
     * @return string
     */
    public function getImgUrl()
    {
        return $this->_captchaData->getImgUrl();
    }

    /**
     * Checks whether captcha was guessed correctly by user
     *
     * @param string $word
     * @return bool
     */
    public function isCorrect($word)
    {
        $storedWord = $this->getWord();
        $this->_clearWord();

        if (!$word || !$storedWord) {
            return false;
        }

        if (!$this->isCaseSensitive()) {
            $storedWord = strtolower($storedWord);
            $word = strtolower($word);
        }
        return $word === $storedWord;
    }

    /**
     * Return full URL to captcha image
     *
     * @return string
     */
    public function getImgSrc()
    {
        return $this->getImgUrl() . $this->getId() . $this->getSuffix();
    }

    /**
     * Log attempt
     *
     * @param string $login
     * @return $this
     */
    public function logAttempt($login)
    {
        if ($this->_isEnabled() && in_array($this->_formId, $this->_getTargetForms())) {
            $this->_getResourceModel()->logAttempt($login);
            if ($this->_isOverLimitLoginAttempts($login)) {
                $this->_session->setData($this->_getFormIdKey('show_captcha'), 1);
            }
        }
        return $this;
    }

    /**
     * Generate word used for captcha render
     *
     * @return string
     */
    protected function _generateWord()
    {
        $word = '';
        $symbols = $this->_getSymbols();
        $wordLen = $this->_getWordLen();
        for ($i = 0; $i < $wordLen; $i++) {
            $word .= $symbols[array_rand($symbols)];
        }
        return $word;
    }

    /**
     * Get symbols array to use for word generation
     *
     * @return array
     */
    protected function _getSymbols()
    {
        return str_split((string)$this->_captchaData->getConfig('symbols'));
    }

    /**
     * Returns length for generating captcha word. This value may be dynamic.
     *
     * @return int
     */
    protected function _getWordLen()
    {
        $from = 0;
        $to = 0;
        $length = (string)$this->_captchaData->getConfig('length');
        if (!is_numeric($length)) {
            if (preg_match('/(\d+)-(\d+)/', $length, $matches)) {
                $from = (int)$matches[1];
                $to = (int)$matches[2];
            }
        } else {
            $from = (int)$length;
            $to = (int)$length;
        }

        if ($to < $from || $from < 1 || $to < 1) {
            $from = self::DEFAULT_WORD_LENGTH_FROM;
            $to = self::DEFAULT_WORD_LENGTH_TO;
        }

        return \Magento\Framework\Math\Random::getRandomNumber($from, $to);
    }

    /**
     * Whether to show captcha for this form every time
     *
     * @return bool
     */
    protected function _isShowAlways()
    {
        if ((string)$this->_captchaData->getConfig('mode') == \Magento\Captcha\Helper\Data::MODE_ALWAYS) {
            return true;
        }

        if ((string)$this->_captchaData->getConfig(
            'mode'
        ) == \Magento\Captcha\Helper\Data::MODE_AFTER_FAIL && $this->_getAllowedAttemptsForSameLogin() == 0
        ) {
            return true;
        }

        $alwaysFor = $this->_captchaData->getConfig('always_for');
        foreach ($alwaysFor as $nodeFormId => $isAlwaysFor) {
            if ($isAlwaysFor && $this->_formId == $nodeFormId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether captcha is enabled at this area
     *
     * @return bool
     */
    protected function _isEnabled()
    {
        return (string)$this->_captchaData->getConfig('enable');
    }

    /**
     * Retrieve list of forms where captcha must be shown
     *
     * For frontend this list is based on current website
     *
     * @return array
     */
    protected function _getTargetForms()
    {
        $formsString = (string)$this->_captchaData->getConfig('forms');
        return explode(',', $formsString);
    }

    /**
     * Get captcha word
     *
     * @return string
     */
    public function getWord()
    {
        $sessionData = $this->_session->getData($this->_getFormIdKey(self::SESSION_WORD));
        return time() < $sessionData['expires'] ? $sessionData['data'] : null;
    }

    /**
     * Set captcha word
     *
     * @param  string $word
     * @return $this
     */
    protected function _setWord($word)
    {
        $this->_session->setData(
            $this->_getFormIdKey(self::SESSION_WORD),
            ['data' => $word, 'expires' => time() + $this->getTimeout()]
        );
        $this->_word = $word;
        return $this;
    }

    /**
     * Set captcha word
     *
     * @return $this
     */
    protected function _clearWord()
    {
        $this->_session->unsetData($this->_getFormIdKey(self::SESSION_WORD));
        $this->_word = null;
        return $this;
    }

    /**
     * Override function to generate less curly captcha that will not cut off
     *
     * @see \Zend_Captcha_Image::_randomSize()
     * @return int
     */
    protected function _randomSize()
    {
        return \Magento\Framework\Math\Random::getRandomNumber(280, 300) / 100;
    }

    /**
     * Overlap of the parent method
     *
     * @return void
     *
     * Now deleting old captcha images make crontab script
     * @see \Magento\Captcha\Model\Observer::deleteExpiredImages
     */
    protected function _gc()
    {
        //do nothing
    }

    /**
     * Get resource model
     *
     * @return \Magento\Captcha\Model\Resource\Log
     */
    protected function _getResourceModel()
    {
        return $this->_resLogFactory->create();
    }
}
