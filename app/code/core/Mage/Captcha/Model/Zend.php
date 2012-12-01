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
 * @category    Mage
 * @package     Mage_Captcha
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Implementation of Zend_Captcha
 *
 * @category   Mage
 * @package    Mage_Captcha
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Captcha_Model_Zend extends Zend_Captcha_Image implements Mage_Captcha_Model_Interface
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
    const DEFAULT_WORD_LENGTH_TO   = 5;

    /**
     * Helper Instance
     * @var Mage_Captcha_Helper_Data
     */
    protected $_helper = null;

    /**
     * Captcha expire time
     * @var int
     */
    protected $_expiration;

    /**
    * Override default value to prevent a captcha cut off
    * @var int
    * @see Zend_Captcha_Image::$_fsize
    */
    protected $_fsize = 22;

    /**
     * Captcha form id
     * @var string
     */
    protected  $_formId;


    /**
     * @var Mage_Captcha_Model_Resource_Log
     */
    protected $_resourceModel;

    /**
     * @var
     */
    protected $_session;

    /**
     * Zend captcha constructor
     *
     * @param array $params
     */
    public function __construct($params)
    {
        if (!is_array($params) || !isset($params['formId'])) {
            throw new Exception('formId is mandatory');
        }

        $this->_formId = $params['formId'];
        $this->_helper = isset($params['helper']) ? $params['helper'] : null;
        $this->_resourceModel = isset($params['resourceModel']) ? $params['resourceModel'] : null;
        $this->_session = isset($params['session']) ? $params['session'] : null;
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
        return 'Mage_Captcha_Block_Captcha_Zend';
    }


    /**
     * Whether captcha is required to be inserted to this form
     *
     * @param null|string $login
     * @return bool
     */
    public function isRequired($login = null)
    {
        if ($this->_isUserAuth() || !$this->_isEnabled() || !in_array($this->_formId, $this->_getTargetForms())) {
            return false;
        }

        return ($this->_isShowAlways() || $this->_isOverLimitAttempts($login)
            || $this->getSession()->getData($this->_getFormIdKey('show_captcha'))
        );
    }

    /**
     * Check is overlimit attempts
     *
     * @param string $login
     * @return bool
     */
    protected function _isOverLimitAttempts($login)
    {
        return ($this->_isOverLimitIpAttempt() || $this->_isOverLimitLoginAttempts($login));
    }

    /**
     * Returns number of allowed attempts for same login
     *
     * @return int
     */
    protected function _getAllowedAttemptsForSameLogin()
    {
        return (int)$this->_getHelper()->getConfigNode('failed_attempts_login');
    }

    /**
     * Returns number of allowed attempts from same IP
     *
     * @return int
     */
    protected function _getAllowedAttemptsFromSameIp()
    {
        return (int)$this->_getHelper()->getConfigNode('failed_attempts_ip');
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
            return ($countAttemptsByLogin >= $this->_getAllowedAttemptsForSameLogin());
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
        return $this->getSession()->isLoggedIn();
    }

    /**
     * Whether to respect case while checking the answer
     *
     * @return bool
     */
    public function isCaseSensitive()
    {
        return (string)$this->_getHelper()->getConfigNode('case_sensitive');
    }

    /**
     * Get font to use when generating captcha
     *
     * @return string
     */
    public function getFont()
    {
        $font = (string)$this->_getHelper()->getConfigNode('font');
        $fonts = $this->_getHelper()->getFonts();

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
            $this->_expiration = (int)$this->_getHelper()->getConfigNode('timeout') * 60;
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
        return $this->_getHelper()->getImgDir();
    }

    /**
     * Get captcha image base URL
     *
     * @return string
     */
    public function getImgUrl()
    {
        return $this->_getHelper()->getImgUrl();
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

        if (!$word || !$storedWord){
            return false;
        }

        if (!$this->isCaseSensitive()) {
            $storedWord = strtolower($storedWord);
            $word = strtolower($word);
        }
        return $word === $storedWord;
    }

    /**
     * Returns session instance
     *
     * @return Mage_Customer_Model_Session|Mage_Backend_Model_Auth_Session
     */
    public function getSession()
    {
        if (empty($this->_session)) {
            $this->_session =  Mage::app()->getStore()->isAdmin()
                ? Mage::getSingleton('Mage_Backend_Model_Auth_Session')
                : Mage::getSingleton('Mage_Customer_Model_Session');
        }
        return $this->_session;
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
     * log Attempt
     *
     * @param string $login
     * @return Mage_Captcha_Model_Zend
     */
    public function logAttempt($login)
    {
        if ($this->_isEnabled() && in_array($this->_formId, $this->_getTargetForms())) {
            $this->_getResourceModel()->logAttempt($login);
            if ($this->_isOverLimitLoginAttempts($login)) {
                $this->getSession()->setData($this->_getFormIdKey('show_captcha'), 1);
            }
        }
        return $this;
    }

    /**
     * Returns captcha helper
     *
     * @return Mage_Captcha_Helper_Data
     */
    protected function _getHelper()
    {
        if (empty($this->_helper)) {
            $this->_helper = Mage::helper('Mage_Captcha_Helper_Data');
        }
        return $this->_helper;
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
        return str_split((string)$this->_getHelper()->getConfigNode('symbols'));
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
        $length = (string)$this->_getHelper()->getConfigNode('length');
        if (!is_numeric($length)) {
            if (preg_match('/(\d+)-(\d+)/', $length, $matches)) {
                $from = (int)$matches[1];
                $to = (int)$matches[2];
            }
        } else {
            $from = (int)$length;
            $to = (int)$length;
        }

        if (($to < $from) || ($from < 1) || ($to < 1)) {
            $from = self::DEFAULT_WORD_LENGTH_FROM;
            $to = self::DEFAULT_WORD_LENGTH_TO;
        }

        return mt_rand($from, $to);
    }

    /**
     * Whether to show captcha for this form every time
     *
     * @return bool
     */
    protected function _isShowAlways()
    {
        if ((string)$this->_getHelper()->getConfigNode('mode') == Mage_Captcha_Helper_Data::MODE_ALWAYS) {
            return true;
        }

        if ((string)$this->_getHelper()->getConfigNode('mode') == Mage_Captcha_Helper_Data::MODE_AFTER_FAIL
            && $this->_getAllowedAttemptsForSameLogin() == 0
        ) {
            return true;
        }

        $alwaysFor = $this->_getHelper()->getConfigNode('always_for');
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
        return (string)$this->_getHelper()->getConfigNode('enable');
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
        $formsString = (string) $this->_getHelper()->getConfigNode('forms');
        return explode(',', $formsString);
    }

    /**
     * Get captcha word
     *
     * @return string
     */
    public function getWord()
    {
        $sessionData = $this->getSession()->getData($this->_getFormIdKey(self::SESSION_WORD));
        return time() < $sessionData['expires'] ? $sessionData['data'] : null;
    }

    /**
     * Set captcha word
     *
     * @param  string $word
     * @return Zend_Captcha_Word
     */
    protected function _setWord($word)
    {
        $this->getSession()->setData($this->_getFormIdKey(self::SESSION_WORD),
            array('data' => $word, 'expires' => time() + $this->getTimeout())
        );
        $this->_word = $word;
        return $this;
    }

    /**
     * Set captcha word
     *
     * @return Mage_Captcha_Model_Zend
     */
    protected function _clearWord()
    {
        $this->getSession()->unsetData($this->_getFormIdKey(self::SESSION_WORD));
        $this->_word = null;
        return $this;
    }

    /**
    * Override function to generate less curly captcha that will not cut off
    *
    * @see Zend_Captcha_Image::_randomSize()
    * @return int
    */
    protected function _randomSize()
    {
        return mt_rand(280, 300) / 100;
    }

    /**
     * Overlap of the parent method
     *
     * Now deleting old captcha images make crontab script
     * @see Mage_Captcha_Model_Observer::deleteExpiredImages
     */
    protected function _gc()
    {
        //do nothing
    }

    /**
     * Get Resource Model
     * @return Mage_Captcha_Model_Resource_Log
     */
    protected function _getResourceModel()
    {
        if (empty($this->_resourceModel)) {
            $this->_resourceModel = Mage::getResourceModel('Mage_Captcha_Model_Resource_Log');
        }
        return $this->_resourceModel;
    }
}
