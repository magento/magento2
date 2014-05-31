<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Captcha
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** @see Zend_Captcha_Base */
#require_once 'Zend/Captcha/Base.php';

/**
 * Word-based captcha adapter
 *
 * Generates random word which user should recognise
 *
 * @category   Zend
 * @package    Zend_Captcha
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Word.php 21793 2010-04-08 00:51:31Z stas $
 */
abstract class Zend_Captcha_Word extends Zend_Captcha_Base
{
    /**#@+
     * @var array Character sets
     */
    static $V  = array("a", "e", "i", "o", "u", "y");
    static $VN = array("a", "e", "i", "o", "u", "y","2","3","4","5","6","7","8","9");
    static $C  = array("b","c","d","f","g","h","j","k","m","n","p","q","r","s","t","u","v","w","x","z");
    static $CN = array("b","c","d","f","g","h","j","k","m","n","p","q","r","s","t","u","v","w","x","z","2","3","4","5","6","7","8","9");
    /**#@-*/

    /**
     * Random session ID
     *
     * @var string
     */
    protected $_id;

    /**
     * Generated word
     *
     * @var string
     */
    protected $_word;

    /**
     * Session
     *
     * @var Zend_Session_Namespace
     */
    protected $_session;

    /**
     * Class name for sessions
     *
     * @var string
     */
    protected $_sessionClass = 'Zend_Session_Namespace';

    /**
     * Should the numbers be used or only letters
     *
     * @var boolean
     */
    protected $_useNumbers = true;

    /**
     * Should both cases be used or only lowercase
     *
     * @var boolean
     */
    // protected $_useCase = false;

    /**
     * Session lifetime for the captcha data
     *
     * @var integer
     */
    protected $_timeout = 300;

    /**
     * Should generate() keep session or create a new one?
     *
     * @var boolean
     */
    protected $_keepSession = false;

    /**#@+
     * Error codes
     */
    const MISSING_VALUE = 'missingValue';
    const MISSING_ID    = 'missingID';
    const BAD_CAPTCHA   = 'badCaptcha';
    /**#@-*/

    /**
     * Error messages
     * @var array
     */
    protected $_messageTemplates = array(
        self::MISSING_VALUE => 'Empty captcha value',
        self::MISSING_ID    => 'Captcha ID field is missing',
        self::BAD_CAPTCHA   => 'Captcha value is wrong',
    );

    /**
     * Length of the word to generate
     *
     * @var integer
     */
    protected $_wordlen = 8;

    /**
     * Retrieve session class to utilize
     *
     * @return string
     */
	public function getSessionClass()
    {
        return $this->_sessionClass;
    }

    /**
     * Set session class for persistence
     *
     * @param  string $_sessionClass
     * @return Zend_Captcha_Word
     */
    public function setSessionClass($_sessionClass)
    {
        $this->_sessionClass = $_sessionClass;
        return $this;
    }

    /**
     * Retrieve word length to use when genrating captcha
     *
     * @return integer
     */
    public function getWordlen()
    {
        return $this->_wordlen;
    }

    /**
     * Set word length of captcha
     *
     * @param integer $wordlen
     * @return Zend_Captcha_Word
     */
    public function setWordlen($wordlen)
    {
        $this->_wordlen = $wordlen;
        return $this;
    }

    /**
     * Retrieve captcha ID
     *
     * @return string
     */
    public function getId ()
    {
        if (null === $this->_id) {
            $this->_setId($this->_generateRandomId());
        }
        return $this->_id;
    }

    /**
     * Set captcha identifier
     *
     * @param string $id
     * return Zend_Captcha_Word
     */
    protected function _setId ($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * Set timeout for session token
     *
     * @param  int $ttl
     * @return Zend_Captcha_Word
     */
    public function setTimeout($ttl)
    {
        $this->_timeout = (int) $ttl;
        return $this;
    }

    /**
     * Get session token timeout
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->_timeout;
    }

	/**
	 * Sets if session should be preserved on generate()
	 *
	 * @param $keepSession Should session be kept on generate()?
	 * @return Zend_Captcha_Word
	 */
	public function setKeepSession($keepSession)
	{
		$this->_keepSession = $keepSession;
		return $this;
	}

    /**
     * Numbers should be included in the pattern?
     *
     * @return bool
     */
    public function getUseNumbers()
    {
        return $this->_useNumbers;
    }

	/**
	 * Set if numbers should be included in the pattern
	 *
     * @param $_useNumbers numbers should be included in the pattern?
     * @return Zend_Captcha_Word
     */
    public function setUseNumbers($_useNumbers)
    {
        $this->_useNumbers = $_useNumbers;
        return $this;
    }

	/**
     * Get session object
     *
     * @return Zend_Session_Namespace
     */
    public function getSession()
    {
        if (!isset($this->_session) || (null === $this->_session)) {
            $id = $this->getId();
            if (!class_exists($this->_sessionClass)) {
                #require_once 'Zend/Loader.php';
                Zend_Loader::loadClass($this->_sessionClass);
            }
            $this->_session = new $this->_sessionClass('Zend_Form_Captcha_' . $id);
            $this->_session->setExpirationHops(1, null, true);
            $this->_session->setExpirationSeconds($this->getTimeout());
        }
        return $this->_session;
    }

    /**
     * Set session namespace object
     *
     * @param  Zend_Session_Namespace $session
     * @return Zend_Captcha_Word
     */
    public function setSession(Zend_Session_Namespace $session)
    {
        $this->_session = $session;
        if($session) {
            $this->_keepSession = true;
        }
        return $this;
    }

    /**
     * Get captcha word
     *
     * @return string
     */
    public function getWord()
    {
        if (empty($this->_word)) {
            $session     = $this->getSession();
            $this->_word = $session->word;
        }
        return $this->_word;
    }

    /**
     * Set captcha word
     *
     * @param  string $word
     * @return Zend_Captcha_Word
     */
    protected function _setWord($word)
    {
        $session       = $this->getSession();
        $session->word = $word;
        $this->_word   = $word;
        return $this;
    }

    /**
     * Generate new random word
     *
     * @return string
     */
    protected function _generateWord()
    {
        $word       = '';
        $wordLen    = $this->getWordLen();
        $vowels     = $this->_useNumbers ? self::$VN : self::$V;
        $consonants = $this->_useNumbers ? self::$CN : self::$C;

        for ($i=0; $i < $wordLen; $i = $i + 2) {
            // generate word with mix of vowels and consonants
            $consonant = $consonants[array_rand($consonants)];
            $vowel     = $vowels[array_rand($vowels)];
            $word     .= $consonant . $vowel;
        }

        if (strlen($word) > $wordLen) {
            $word = substr($word, 0, $wordLen);
        }

        return $word;
    }

    /**
     * Generate new session ID and new word
     *
     * @return string session ID
     */
    public function generate()
    {
        if(!$this->_keepSession) {
            $this->_session = null;
        }
        $id = $this->_generateRandomId();
        $this->_setId($id);
        $word = $this->_generateWord();
        $this->_setWord($word);
        return $id;
    }

    protected function _generateRandomId()
    {
        return md5(mt_rand(0, 1000) . microtime(true));
    }

    /**
     * Validate the word
     *
     * @see    Zend_Validate_Interface::isValid()
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        if (!is_array($value) && !is_array($context)) {
            $this->_error(self::MISSING_VALUE);
            return false;
        }
        if (!is_array($value) && is_array($context)) {
            $value = $context;
        }

        $name = $this->getName();

        if (isset($value[$name])) {
            $value = $value[$name];
        }

        if (!isset($value['input'])) {
            $this->_error(self::MISSING_VALUE);
            return false;
        }
        $input = strtolower($value['input']);
        $this->_setValue($input);

        if (!isset($value['id'])) {
            $this->_error(self::MISSING_ID);
            return false;
        }

        $this->_id = $value['id'];
        if ($input !== $this->getWord()) {
            $this->_error(self::BAD_CAPTCHA);
            return false;
        }

        return true;
    }

    /**
     * Get captcha decorator
     *
     * @return string
     */
    public function getDecorator()
    {
        return "Captcha_Word";
    }
}
