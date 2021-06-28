<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Captcha\Model;

use Laminas\Math\Rand;
use Laminas\Session\Container;
use Laminas\Stdlib\ErrorHandler;
use Laminas\Validator\AbstractValidator;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Captcha\Block\Captcha\DefaultCaptcha;
use Magento\Captcha\Helper\Data;
use Magento\Captcha\Model\ResourceModel\Log;
use Magento\Captcha\Model\ResourceModel\LogFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Magento\Framework\Session\SessionManagerInterface;
use Traversable;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 *
 * @api
 * @since 100.0.2
 */
class DefaultModel extends AbstractValidator implements CaptchaInterface
{
    // @codingStandardsIgnoreStart
    /**#@+
     * @var array Character sets
     */
    public static $V = ['a', 'e', 'i', 'o', 'u', 'y'];

    public static $VN = ['a', 'e', 'i', 'o', 'u', 'y', '2', '3', '4', '5', '6', '7', '8', '9'];

    public static $C = [
        'b',
        'c',
        'd',
        'f',
        'g',
        'h',
        'j',
        'k',
        'm',
        'n',
        'p',
        'q',
        'r',
        's',
        't',
        'u',
        'v',
        'w',
        'x',
        'z',
    ];

    public static $CN = [
        'b',
        'c',
        'd',
        'f',
        'g',
        'h',
        'j',
        'k',
        'm',
        'n',
        'p',
        'q',
        'r',
        's',
        't',
        'u',
        'v',
        'w',
        'x',
        'z',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
    ];

    /**#@-*/
    // @codingStandardsIgnoreEnd

    /**
     * Random session ID
     *
     * @var string
     */
    protected $id;

    /**
     * Generated word
     *
     * @var string
     */
    protected $word;

    /**
     * Class name for sessions
     *
     * @var string
     */
    protected $sessionClass = 'Laminas\Session\Container';

    /**
     * Should the numbers be used or only letters
     *
     * @var bool
     */
    protected $useNumbers = true;

    /**
     * Should both cases be used or only lowercase
     *
     * @var bool
     */
    // protected $useCase = false;

    /**
     * Session lifetime for the captcha data
     *
     * @var int
     */
    protected $timeout = 300;

    /**#@+
     * Error codes
     */
    const MISSING_VALUE = 'missingValue';
    const MISSING_ID = 'missingID';
    const BAD_CAPTCHA = 'badCaptcha';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $messageTemplates = [
        self::MISSING_VALUE => 'Empty captcha value',
        self::MISSING_ID => 'Captcha ID field is missing',
        self::BAD_CAPTCHA => 'Captcha value is wrong',
    ];

    /**
     * Length of the word to generate
     *
     * @var int
     */
    protected $wordlen = 8;

    /**
     * Directory for generated images
     *
     * @var string
     */
    protected $imgDir = 'public/images/captcha/';

    /**
     * URL for accessing images
     *
     * @var string
     */
    protected $imgUrl = '/images/captcha/';

    /**
     * Image's alt tag content
     *
     * @var string
     */
    protected $imgAlt = '';

    /**
     * Image suffix (including dot)
     *
     * @var string
     */
    protected $suffix = '.png';

    /**
     * Image width
     *
     * @var int
     */
    protected $width = 200;

    /**
     * Image height
     *
     * @var int
     */
    protected $height = 50;

    /**
     * Font size
     *
     * @var int
     */
    protected $fsize = 24;

    /**
     * Image font file
     *
     * @var string
     */
    protected $font;

    /**
     * Image to use as starting point
     * Default is blank image. If provided, should be PNG image.
     *
     * @var string
     */
    protected $startImage;

    /**
     * How frequently to execute garbage collection
     *
     * @var int
     */
    protected $gcFreq = 10;

    /**
     * How long to keep generated images
     *
     * @var int
     */
    protected $expiration = 600;

    /**
     * Number of noise dots on image
     * Used twice - before and after transform
     *
     * @var int
     */
    protected $dotNoiseLevel = 100;

    /**
     * Number of noise lines on image
     * Used twice - before and after transform
     *
     * @var int
     */
    protected $lineNoiseLevel = 5;

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
     * @var Data
     * @since 100.2.0
     */
    protected $captchaData;

    /**
     * Captcha form id
     *
     * @var string
     * @since 100.2.0
     */
    protected $formId;

    /**
     * @var LogFactory
     * @since 100.2.0
     */
    protected $resLogFactory;

    /**
     * Overrides parent parameter as session comes in constructor.
     *
     * @var bool
     * @since 100.2.0
     */
    protected $keepSession = true;

    /**
     * @var SessionManagerInterface
     * @since 100.2.0
     */
    protected $session;

    /**
     * @var string
     */
    private $words;

    /**
     * @var Random
     */
    private $randomMath;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * Captcha name
     *
     * Useful to generate/check form fields
     *
     * @var string
     */
    protected $name;

    /**
     * Captcha options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Options to skip when processing options
     *
     * @var array
     */
    protected $skipOptions = [
        'options',
        'config',
    ];

    /**
     * @param SessionManagerInterface $session
     * @param Data $captchaData
     * @param LogFactory $resLogFactory
     * @param string $formId
     * @param Random|null $randomMath
     * @param UserContextInterface|null $userContext
     * @throws LocalizedException
     */
    public function __construct(
        SessionManagerInterface $session,
        Data $captchaData,
        LogFactory $resLogFactory,
        string $formId,
        Random $randomMath = null,
        ?UserContextInterface $userContext = null
    ) {
        parent::__construct();
        if (!extension_loaded('gd')) {
            throw new LocalizedException(__('Image CAPTCHA requires GD extension'));
        }

        if (!function_exists('imagepng')) {
            throw new LocalizedException(__('Image CAPTCHA requires PNG support'));
        }

        if (!function_exists('imageftbbox')) {
            throw new LocalizedException(__('Image CAPTCHA requires FT fonts support'));
        }

        if (isset($this->messageTemplates)) {
            $this->abstractOptions['messageTemplates'] = $this->messageTemplates;
        }

        if (isset($this->messageVariables)) {
            $this->abstractOptions['messageVariables'] = $this->messageVariables;
        }

        $this->session = $session;
        $this->captchaData = $captchaData;
        $this->resLogFactory = $resLogFactory;
        $this->formId = $formId;
        $this->randomMath = $randomMath ?? ObjectManager::getInstance()->get(Random::class);
        $this->userContext = $userContext ?? ObjectManager::getInstance()->get(UserContextInterface::class);
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getImgAlt()
    {
        return $this->imgAlt;
    }

    /**
     * @return string
     */
    public function getStartImage()
    {
        return $this->startImage;
    }

    /**
     * @return int
     */
    public function getDotNoiseLevel()
    {
        return $this->dotNoiseLevel;
    }

    /**
     * @return int
     */
    public function getLineNoiseLevel()
    {
        return $this->lineNoiseLevel;
    }

    /**
     * Get garbage collection frequency
     *
     * @return int
     */
    public function getGcFreq()
    {
        return $this->gcFreq;
    }

    /**
     * Get font size
     *
     * @return int
     */
    public function getFontSize()
    {
        return $this->fsize;
    }

    /**
     * Get captcha image height
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Get captcha image file suffix
     *
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * Get captcha image width
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param string $startImage
     */
    public function setStartImage($startImage)
    {
        $this->startImage = $startImage;
        return $this;
    }

    /**
     * @param int $dotNoiseLevel
     */
    public function setDotNoiseLevel($dotNoiseLevel)
    {
        $this->dotNoiseLevel = $dotNoiseLevel;
        return $this;
    }

    /**
     * @param int $lineNoiseLevel
     */
    public function setLineNoiseLevel($lineNoiseLevel)
    {
        $this->lineNoiseLevel = $lineNoiseLevel;
        return $this;
    }

    /**
     * Set captcha expiration
     *
     * @param int $expiration
     */
    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;
        return $this;
    }

    /**
     * Set garbage collection frequency
     *
     * @param int $gcFreq
     */
    public function setGcFreq($gcFreq)
    {
        $this->gcFreq = $gcFreq;
        return $this;
    }

    /**
     * Set captcha font
     *
     * @param string $font
     */
    public function setFont($font)
    {
        $this->font = $font;
        return $this;
    }

    /**
     * Set captcha font size
     *
     * @param int $fsize
     */
    public function setFontSize($fsize)
    {
        $this->fsize = $fsize;
        return $this;
    }

    /**
     * Set captcha image height
     *
     * @param int $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * Set captcha image storage directory
     *
     * @param string $imgDir
     */
    public function setImgDir($imgDir)
    {
        $this->imgDir = rtrim($imgDir, "/\\") . '/';
        return $this;
    }

    /**
     * Set captcha image base URL
     *
     * @param string $imgUrl
     */
    public function setImgUrl($imgUrl)
    {
        $this->imgUrl = rtrim($imgUrl, "/\\") . '/';
        return $this;
    }

    /**
     * @param string $imgAlt
     */
    public function setImgAlt($imgAlt)
    {
        $this->imgAlt = $imgAlt;
        return $this;
    }

    /**
     * Set captcha image filename suffix
     *
     * @param string $suffix
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
        return $this;
    }

    /**
     * Set captcha image width
     *
     * @param int $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * Generate random frequency
     *
     * @return float
     * @throws \Exception
     */
    protected function randomFreq()
    {
        return random_int(700000, 1000000) / 15000000;
    }

    /**
     * Generate random phase
     *
     * @return float
     * @throws \Exception
     */
    protected function randomPhase()
    {
        // random phase from 0 to pi
        return random_int(0, 3141592) / 1000000;
    }

    /**
     * Generate captcha
     *
     * @return string captcha ID
     * @throws LocalizedException
     * @throws \Exception
     */
    public function generate()
    {
        if (!$this->keepSession) {
            $this->session = null;
        }
        $id = $this->generateRandomId();
        $this->setId($id);
        $word = $this->generateWord();
        $this->setWord($word);
        $tries = 5;

        // If there's already such file, try creating a new ID
        while ($tries-- && file_exists($this->getImgDir() . $id . $this->getSuffix())) {
            $id = $this->generateRandomId();
            $this->setId($id);
        }
        $this->generateImage($id, $this->getWord());

        if (random_int(1, $this->getGcFreq()) == 1) {
            $this->gc();
        }

        return $id;
    }

    /**
     * Get helper name used to render captcha
     *
     * @return string
     */
    public function getHelperName()
    {
        return 'captcha/image';
    }

    /**
     * Get Block Name
     *
     * @return string
     */
    public function getBlockName()
    {
        return DefaultCaptcha::class;
    }

    /**
     * Whether captcha is required to be inserted to this form
     *
     * @param null|string $login
     * @return bool
     */
    public function isRequired($login = null)
    {
        if (($this->isUserAuth()
                && !$this->isShownToLoggedInUser())
            || !$this->isEnabled()
            || !in_array(
                $this->formId,
                $this->getTargetForms()
            )
            || $this->userContext->getUserType() === UserContextInterface::USER_TYPE_INTEGRATION
        ) {
            return false;
        }

        return $this->isShowAlways()
            || $this->isOverLimitAttempts($login)
            || $this->session->getData($this->getFormIdKey('show_captcha'));
    }

    /**
     * Check if CAPTCHA has to be shown to logged in user on this form
     *
     * @return bool
     */
    public function isShownToLoggedInUser()
    {
        $forms = (array)$this->captchaData->getConfig('shown_to_logged_in_user');
        foreach ($forms as $formId => $isShownToLoggedIn) {
            if ($isShownToLoggedIn && $this->formId == $formId) {
                return true;
            }
        }
        return false;
    }

    /**
     * Whether to respect case while checking the answer
     *
     * @return bool
     */
    public function isCaseSensitive()
    {
        return (string)$this->captchaData->getConfig('case_sensitive');
    }

    /**
     * Get font to use when generating captcha
     *
     * @return string
     */
    public function getFont()
    {
        $font = (string)$this->captchaData->getConfig('font');
        $fonts = $this->captchaData->getFonts();

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
        if (!$this->expiration) {
            /**
             * as "timeout" configuration parameter specifies timeout in minutes - we multiply it on 60 to set
             * expiration in seconds
             */
            $this->expiration = (int)$this->captchaData->getConfig('timeout') * 60;
        }
        return $this->expiration;
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
        return $this->captchaData->getImgDir();
    }

    /**
     * Get captcha image base URL
     *
     * @return string
     */
    public function getImgUrl()
    {
        return $this->captchaData->getImgUrl();
    }

    /**
     * Checks whether captcha was guessed correctly by user
     *
     * @param string $word
     * @return bool
     */
    public function isCorrect($word)
    {
        $storedWords = $this->getWords();
        $this->clearWord();

        if (!$word || !$storedWords) {
            return false;
        }

        if (!$this->isCaseSensitive()) {
            $storedWords = strtolower($storedWords);
            $word = strtolower($word);
        }
        return in_array($word, explode(',', $storedWords));
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
        if ($this->isEnabled() && in_array($this->formId, $this->getTargetForms())) {
            $this->getResourceModel()->logAttempt($login);
            if ($this->isOverLimitLoginAttempts($login)) {
                $this->setShowCaptchaInSession(true);
            }
        }
        return $this;
    }

    /**
     * Set show_captcha flag in session
     *
     * @param bool $value
     * @return void
     * @since 100.1.0
     */
    public function setShowCaptchaInSession($value = true)
    {
        if ($value !== true) {
            $value = false;
        }

        $this->session->setData($this->getFormIdKey('show_captcha'), $value);
    }

    /**
     * Returns length for generating captcha word. This value may be dynamic.
     *
     * @return int
     * @throws LocalizedException
     * @since 100.2.0
     */
    public function getWordLen()
    {
        $from = 0;
        $to = 0;
        $length = (string)$this->captchaData->getConfig('length');
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

        return Random::getRandomNumber($from, $to);
    }

    /**
     * Get captcha word
     *
     * @return string|null
     */
    public function getWord()
    {
        $sessionData = $this->session->getData($this->getFormIdKey(self::SESSION_WORD));
        return time() < $sessionData['expires'] ? $sessionData['data'] : null;
    }

    /**
     * Get captcha words
     *
     * @return string
     */
    private function getWords()
    {
        $sessionData = $this->session->getData($this->getFormIdKey(self::SESSION_WORD));
        $words = '';
        if (isset($sessionData['expires'], $sessionData['words']) && time() < $sessionData['expires']) {
            $words = $sessionData['words'];
        }

        return $words;
    }

    /**
     * Retrieve session class to utilize
     *
     * @return string
     */
    public function getSessionClass()
    {
        return $this->sessionClass;
    }

    /**
     * Set session class for persistence
     *
     * @param string $sessionClass
     */
    public function setSessionClass($sessionClass)
    {
        $this->sessionClass = $sessionClass;
        return $this;
    }

    /**
     * Set word length of captcha
     *
     * @param int $wordlen
     */
    public function setWordlen($wordlen)
    {
        $this->wordlen = $wordlen;
        return $this;
    }

    /**
     * Retrieve captcha ID
     *
     * @return string
     */
    public function getId()
    {
        if (null === $this->id) {
            $this->setId($this->generateRandomId());
        }
        return $this->id;
    }

    /**
     * Set timeout for session token
     *
     * @param int $ttl
     */
    public function setTimeout($ttl)
    {
        $this->timeout = (int)$ttl;
        return $this;
    }

    /**
     * Sets if session should be preserved on generate()
     *
     * @param bool $keepSession Should session be kept on generate()?
     */
    public function setKeepSession($keepSession)
    {
        $this->keepSession = $keepSession;
        return $this;
    }

    /**
     * Numbers should be included in the pattern?
     *
     * @return bool
     */
    public function getUseNumbers()
    {
        return $this->useNumbers;
    }

    /**
     * Set if numbers should be included in the pattern
     *
     * @param bool $useNumbers numbers should be included in the pattern?
     */
    public function setUseNumbers($useNumbers)
    {
        $this->useNumbers = $useNumbers;
        return $this;
    }

    /**
     * Get session object
     *
     * @return Container
     * @throws InvalidArgumentException
     */
    public function getSession()
    {
        if (!isset($this->session) || (null === $this->session)) {
            $id = $this->getId();
            if (!class_exists($this->sessionClass)) {
                throw new InvalidArgumentException(__("Session class $this->sessionClass not found"));
            }
            $this->session = new $this->sessionClass('Laminas_Form_Captcha_' . $id);
            $this->session->setExpirationHops(1, null);
            $this->session->setExpirationSeconds($this->getTimeout());
        }
        return $this->session;
    }

    /**
     * Set session namespace object
     *
     * @param Container $session
     */
    public function setSession(Container $session)
    {
        $this->session = $session;
        if ($session) {
            $this->keepSession = true;
        }
        return $this;
    }

    /**
     * Validate the word
     *
     * @param mixed $value
     * @param mixed $context
     * @return bool
     * @see    \Laminas\Validator\ValidatorInterface::isValid()
     */
    public function isValid($value, $context = null)
    {
        if (!is_array($value)) {
            if (!is_array($context)) {
                $this->error(self::MISSING_VALUE);
                return false;
            }
            $value = $context;
        }

        $name = $this->getName();

        if (isset($value[$name])) {
            $value = $value[$name];
        }

        if (!isset($value['input'])) {
            $this->error(self::MISSING_VALUE);
            return false;
        }
        $input = strtolower($value['input']);
        $this->setValue($input);

        if (!isset($value['id'])) {
            $this->error(self::MISSING_ID);
            return false;
        }

        $this->id = $value['id'];
        if ($input !== $this->getWord()) {
            $this->error(self::BAD_CAPTCHA);
            return false;
        }

        return true;
    }

    /**
     * Set single option for the object
     *
     * @param string $key
     * @param string $value
     */
    public function setOption($key, $value)
    {
        if (in_array(strtolower($key), $this->skipOptions)) {
            return $this;
        }

        $method = 'set' . ucfirst($key);
        if (method_exists($this, $method)) {
            // Setter exists; use it
            $this->$method($value);
            $this->options[$key] = $value;
        } elseif (property_exists($this, $key)) {
            // Assume it's metadata
            $this->$key = $value;
            $this->options[$key] = $value;
        }
        return $this;
    }

    /**
     * Set object state from options array
     *
     * @param array|Traversable $options
     * @throws InvalidArgumentException
     */
    public function setOptions($options = [])
    {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new InvalidArgumentException(__(__METHOD__ . ' expects an array or Traversable'));
        }

        foreach ($options as $key => $value) {
            $this->setOption($key, $value);
        }
        return $this;
    }

    /**
     * Retrieve options representing object state
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Generate a random identifier
     *
     * @return string
     */
    protected function generateRandomId()
    {
        return hash('sha256', Rand::getBytes(32));
    }

    /**
     * Set captcha identifier
     *
     * @param string $id
     */
    protected function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Overlap of the parent method
     *
     * @return void
     *
     * Now deleting old captcha images make crontab script
     * @see \Magento\Captcha\Cron\DeleteExpiredImages::execute
     *
     * Added SuppressWarnings since this method is declared in parent class and we can not use other method name.
     * @SuppressWarnings(PHPMD.ShortMethodName)
     * @since 100.2.0
     */
    protected function gc()
    {
        return; // required for static testing to pass
    }

    /**
     * Override function to generate less curly captcha that will not cut off
     *
     * @return int
     * @throws LocalizedException
     * @since 100.2.0
     */
    protected function randomSize()
    {
        return Random::getRandomNumber(280, 300) / 100;
    }

    /**
     * Generate word used for captcha render
     *
     * @return string
     * @throws LocalizedException
     * @since 100.2.0
     */
    protected function generateWord()
    {
        $symbols = (string)$this->captchaData->getConfig('symbols');
        $wordLen = $this->getWordLen();
        return $this->randomMath->getRandomString($wordLen, $symbols);
    }

    /**
     * Generate image captcha
     *
     * Override this function if you want different image generator
     * Wave transform from http://www.captcha.ru/captchas/multiwave/
     *
     * @param string $id Captcha ID
     * @param string $word Captcha word
     * @throws LocalizedException
     */
    protected function generateImage($id, $word)
    {
        $font = $this->getFont();

        if (empty($font)) {
            throw new LocalizedException(__('Image CAPTCHA requires font'));
        }

        $w = $this->getWidth();
        $h = $this->getHeight();
        $fsize = $this->getFontSize();

        $imgFile = $this->getImgDir() . $id . $this->getSuffix();

        if (empty($this->startImage)) {
            $img = imagecreatetruecolor($w, $h);
        } else {
            // Potential error is change to exception
            ErrorHandler::start();
            $img = imagecreatefrompng($this->startImage);
            $error = ErrorHandler::stop();
            if (!$img || $error) {
                throw new LocalizedException(
                    __("Can not load start image '{$this->startImage}'"),
                    $error,
                    0
                );
            }
            $w = imagesx($img);
            $h = imagesy($img);
        }

        $textColor = imagecolorallocate($img, 0, 0, 0);
        $bgColor = imagecolorallocate($img, 255, 255, 255);
        imagefilledrectangle($img, 0, 0, $w - 1, $h - 1, $bgColor);
        $textbox = imageftbbox($fsize, 0, $font, $word);
        $x = ($w - ($textbox[2] - $textbox[0])) / 2;
        $y = ($h - ($textbox[7] - $textbox[1])) / 2;
        imagefttext($img, $fsize, 0, $x, $y, $textColor, $font, $word);

        // generate noise
        for ($i = 0; $i < $this->dotNoiseLevel; $i++) {
            imagefilledellipse($img, random_int(0, $w), random_int(0, $h), 2, 2, $textColor);
        }
        for ($i = 0; $i < $this->lineNoiseLevel; $i++) {
            imageline($img, random_int(0, $w), random_int(0, $h), random_int(0, $w), random_int(0, $h), $textColor);
        }

        // transformed image
        $img2 = imagecreatetruecolor($w, $h);
        $bgColor = imagecolorallocate($img2, 255, 255, 255);
        imagefilledrectangle($img2, 0, 0, $w - 1, $h - 1, $bgColor);

        // apply wave transforms
        $freq1 = $this->randomFreq();
        $freq2 = $this->randomFreq();
        $freq3 = $this->randomFreq();
        $freq4 = $this->randomFreq();

        $ph1 = $this->randomPhase();
        $ph2 = $this->randomPhase();
        $ph3 = $this->randomPhase();
        $ph4 = $this->randomPhase();

        $szx = $this->randomSize();
        $szy = $this->randomSize();

        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $sx = $x + (sin($x * $freq1 + $ph1) + sin($y * $freq3 + $ph3)) * $szx;
                $sy = $y + (sin($x * $freq2 + $ph2) + sin($y * $freq4 + $ph4)) * $szy;

                if ($sx < 0 || $sy < 0 || $sx >= $w - 1 || $sy >= $h - 1) {
                    continue;
                } else {
                    $color = (imagecolorat($img, $sx, $sy) >> 16) & 0xFF;
                    $colorX = (imagecolorat($img, $sx + 1, $sy) >> 16) & 0xFF;
                    $colorY = (imagecolorat($img, $sx, $sy + 1) >> 16) & 0xFF;
                    $colorXY = (imagecolorat($img, $sx + 1, $sy + 1) >> 16) & 0xFF;
                }

                if ($color == 255 && $colorX == 255 && $colorY == 255 && $colorXY == 255) {
                    // ignore background
                    continue;
                } elseif ($color == 0 && $colorX == 0 && $colorY == 0 && $colorXY == 0) {
                    // transfer inside of the image as-is
                    $newcolor = 0;
                } else {
                    // do antialiasing for border items
                    $fracX = $sx - floor($sx);
                    $fracY = $sy - floor($sy);
                    $fracX1 = 1 - $fracX;
                    $fracY1 = 1 - $fracY;

                    $newcolor = $color * $fracX1 * $fracY1
                        + $colorX * $fracX * $fracY1
                        + $colorY * $fracX1 * $fracY
                        + $colorXY * $fracX * $fracY;
                }

                imagesetpixel($img2, $x, $y, imagecolorallocate($img2, $newcolor, $newcolor, $newcolor));
            }
        }

        // generate noise
        for ($i = 0; $i < $this->dotNoiseLevel; $i++) {
            imagefilledellipse($img2, random_int(0, $w), random_int(0, $h), 2, 2, $textColor);
        }

        for ($i = 0; $i < $this->lineNoiseLevel; $i++) {
            imageline($img2, random_int(0, $w), random_int(0, $h), random_int(0, $w), random_int(0, $h), $textColor);
        }

        imagepng($img2, $imgFile);
        imagedestroy($img);
        imagedestroy($img2);
    }

    /**
     * Set captcha word
     *
     * @param string $word
     * @return $this
     * @since 100.2.0
     */
    protected function setWord($word)
    {
        $this->words = $this->words ? $this->words . ',' . $word : $word;
        $this->session->setData(
            $this->getFormIdKey(self::SESSION_WORD),
            ['data' => $word, 'words' => $this->words, 'expires' => time() + $this->getTimeout()]
        );
        $this->word = $word;
        return $this;
    }

    /**
     * Get resource model
     *
     * @return Log
     */
    private function getResourceModel()
    {
        return $this->resLogFactory->create();
    }

    /**
     * Returns key with respect of current form ID
     *
     * @param string $key
     * @return string
     */
    private function getFormIdKey($key)
    {
        return $this->formId . '_' . $key;
    }

    /**
     * Set captcha word
     *
     * @return $this
     */
    private function clearWord()
    {
        $this->session->unsetData($this->getFormIdKey(self::SESSION_WORD));
        $this->word = null;
        return $this;
    }

    /**
     * Whether to show captcha for this form every time
     *
     * @return bool
     */
    private function isShowAlways()
    {
        $captchaMode = (string)$this->captchaData->getConfig('mode');

        if ($captchaMode === Data::MODE_ALWAYS) {
            return true;
        }

        if ($captchaMode === Data::MODE_AFTER_FAIL
            && $this->getAllowedAttemptsForSameLogin() === 0
        ) {
            return true;
        }

        $alwaysFor = $this->captchaData->getConfig('always_for');
        foreach ($alwaysFor as $nodeFormId => $isAlwaysFor) {
            if ($isAlwaysFor && $this->formId == $nodeFormId) {
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
    private function isEnabled()
    {
        return (string)$this->captchaData->getConfig('enable');
    }

    /**
     * Retrieve list of forms where captcha must be shown
     *
     * For frontend this list is based on current website
     *
     * @return array
     */
    private function getTargetForms()
    {
        $formsString = (string)$this->captchaData->getConfig('forms');
        return explode(',', $formsString);
    }


    /**
     * Check is over limit attempts
     *
     * @param string $login
     * @return bool
     */
    private function isOverLimitAttempts($login)
    {
        return $this->isOverLimitIpAttempt() || $this->isOverLimitLoginAttempts($login);
    }

    /**
     * Returns number of allowed attempts for same login
     *
     * @return int
     */
    private function getAllowedAttemptsForSameLogin()
    {
        return (int)$this->captchaData->getConfig('failed_attempts_login');
    }

    /**
     * Returns number of allowed attempts from same IP
     *
     * @return int
     */
    private function getAllowedAttemptsFromSameIp()
    {
        return (int)$this->captchaData->getConfig('failed_attempts_ip');
    }

    /**
     * Check is over limit saved attempts from one ip
     *
     * @return bool
     */
    private function isOverLimitIpAttempt()
    {
        $countAttemptsByIp = $this->getResourceModel()->countAttemptsByRemoteAddress();
        return $countAttemptsByIp >= $this->getAllowedAttemptsFromSameIp();
    }

    /**
     * Is Over Limit Login Attempts
     *
     * @param string $login
     * @return bool
     */
    private function isOverLimitLoginAttempts($login)
    {
        if ($login != false) {
            $countAttemptsByLogin = $this->getResourceModel()->countAttemptsByUserLogin($login);
            return $countAttemptsByLogin >= $this->getAllowedAttemptsForSameLogin();
        }
        return false;
    }

    /**
     * Check is user auth
     *
     * @return bool
     */
    private function isUserAuth()
    {
        return $this->session->isLoggedIn() || $this->userContext->getUserId();
    }
}
