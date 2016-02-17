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
 * @package    Zend_Translate
 * @subpackage Zend_Translate_Adapter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Locale
 */
#require_once 'Zend/Locale.php';

/**
 * @see Zend_Translate_Plural
 */
#require_once 'Zend/Translate/Plural.php';

/**
 * Basic adapter class for each translation source adapter
 *
 * @category   Zend
 * @package    Zend_Translate
 * @subpackage Zend_Translate_Adapter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Translate_Adapter {
    /**
     * Shows if locale detection is in automatic level
     * @var boolean
     */
    private $_automatic = true;

    /**
     * Internal value to see already routed languages
     * @var array()
     */
    private $_routed = array();

    /**
     * Internal cache for all adapters
     * @var Zend_Cache_Core
     */
    protected static $_cache     = null;

    /**
     * Internal value to remember if cache supports tags
     *
     * @var boolean
     */
    private static $_cacheTags = false;

    /**
     * Scans for the locale within the name of the directory
     * @constant integer
     */
    const LOCALE_DIRECTORY = 'directory';

    /**
     * Scans for the locale within the name of the file
     * @constant integer
     */
    const LOCALE_FILENAME  = 'filename';

    /**
     * Array with all options, each adapter can have own additional options
     *   'clear'           => when true, clears already loaded translations when adding new files
     *   'content'         => content to translate or file or directory with content
     *   'disableNotices'  => when true, omits notices from being displayed
     *   'ignore'          => a prefix for files and directories which are not being added
     *   'locale'          => the actual set locale to use
     *   'log'             => a instance of Zend_Log where logs are written to
     *   'logMessage'      => message to be logged
     *   'logPriority'     => priority which is used to write the log message
     *   'logUntranslated' => when true, untranslated messages are not logged
     *   'reload'          => reloads the cache by reading the content again
     *   'scan'            => searches for translation files using the LOCALE constants
     *   'tag'             => tag to use for the cache
     *
     * @var array
     */
    protected $_options = array(
        'clear'           => false,
        'content'         => null,
        'disableNotices'  => false,
        'ignore'          => '.',
        'locale'          => 'auto',
        'log'             => null,
        'logMessage'      => "Untranslated message within '%locale%': %message%",
        'logPriority'     => 5,
        'logUntranslated' => false,
        'reload'          => false,
        'route'           => null,
        'scan'            => null,
        'tag'             => 'Zend_Translate'
    );

    /**
     * Translation table
     * @var array
     */
    protected $_translate = array();

    /**
     * Generates the adapter
     *
     * @param  string|array|Zend_Config $options Translation options for this adapter
     * @param  string|array [$content]
     * @param  string|Zend_Locale [$locale]
     * @throws Zend_Translate_Exception
     * @return void
     */
    public function __construct($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (func_num_args() > 1) {
            $args               = func_get_args();
            $options            = array();
            $options['content'] = array_shift($args);

            if (!empty($args)) {
                $options['locale'] = array_shift($args);
            }

            if (!empty($args)) {
                $opt     = array_shift($args);
                $options = array_merge($opt, $options);
            }
        } else if (!is_array($options)) {
            $options = array('content' => $options);
        }

        if (array_key_exists('cache', $options)) {
            self::setCache($options['cache']);
            unset($options['cache']);
        }

        if (isset(self::$_cache)) {
            $id = 'Zend_Translate_' . $this->toString() . '_Options';
            $result = self::$_cache->load($id);
            if ($result) {
                $this->_options = $result;
            }
        }

        if (empty($options['locale']) || ($options['locale'] === "auto")) {
            $this->_automatic = true;
        } else {
            $this->_automatic = false;
        }

        $locale = null;
        if (!empty($options['locale'])) {
            $locale = $options['locale'];
            unset($options['locale']);
        }

        $this->setOptions($options);
        $options['locale'] = $locale;

        if (!empty($options['content'])) {
            $this->addTranslation($options);
        }

        if ($this->getLocale() !== (string) $options['locale']) {
            $this->setLocale($options['locale']);
        }
    }

    /**
     * Add translations
     *
     * This may be a new language or additional content for an existing language
     * If the key 'clear' is true, then translations for the specified
     * language will be replaced and added otherwise
     *
     * @param  array|Zend_Config $options Options and translations to be added
     * @throws Zend_Translate_Exception
     * @return Zend_Translate_Adapter Provides fluent interface
     */
    public function addTranslation($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (func_num_args() > 1) {
            $args = func_get_args();
            $options            = array();
            $options['content'] = array_shift($args);

            if (!empty($args)) {
                $options['locale'] = array_shift($args);
            }

            if (!empty($args)) {
                $opt     = array_shift($args);
                $options = array_merge($opt, $options);
            }
        } else if (!is_array($options)) {
            $options = array('content' => $options);
        }

        if (!isset($options['content']) || empty($options['content'])) {
            #require_once 'Zend/Translate/Exception.php';
            throw new Zend_Translate_Exception("Required option 'content' is missing");
        }

        $originate = null;
        if (!empty($options['locale'])) {
            $originate = (string) $options['locale'];
        }

        if ((array_key_exists('log', $options)) && !($options['log'] instanceof Zend_Log)) {
            #require_once 'Zend/Translate/Exception.php';
            throw new Zend_Translate_Exception('Instance of Zend_Log expected for option log');
        }

        try {
            if (!($options['content'] instanceof Zend_Translate) && !($options['content'] instanceof Zend_Translate_Adapter)) {
                if (empty($options['locale'])) {
                    $options['locale'] = null;
                }

                $options['locale'] = Zend_Locale::findLocale($options['locale']);
            }
        } catch (Zend_Locale_Exception $e) {
            #require_once 'Zend/Translate/Exception.php';
            throw new Zend_Translate_Exception("The given Language '{$options['locale']}' does not exist", 0, $e);
        }

        $options  = $options + $this->_options;
        if (is_string($options['content']) and is_dir($options['content'])) {
            $options['content'] = realpath($options['content']);
            $prev = '';
            $iterator = new RecursiveIteratorIterator(
                new RecursiveRegexIterator(
                    new RecursiveDirectoryIterator($options['content'], RecursiveDirectoryIterator::KEY_AS_PATHNAME),
                    '/^(?!.*(\.svn|\.cvs)).*$/', RecursiveRegexIterator::MATCH
                ),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $directory => $info) {
                $file = $info->getFilename();
                if (is_array($options['ignore'])) {
                    foreach ($options['ignore'] as $key => $ignore) {
                        if (strpos($key, 'regex') !== false) {
                            if (preg_match($ignore, $directory)) {
                                // ignore files matching the given regex from option 'ignore' and all files below
                                continue 2;
                            }
                        } else if (strpos($directory, DIRECTORY_SEPARATOR . $ignore) !== false) {
                            // ignore files matching first characters from option 'ignore' and all files below
                            continue 2;
                        }
                    }
                } else {
                    if (strpos($directory, DIRECTORY_SEPARATOR . $options['ignore']) !== false) {
                        // ignore files matching first characters from option 'ignore' and all files below
                        continue;
                    }
                }

                if ($info->isDir()) {
                    // pathname as locale
                    if (($options['scan'] === self::LOCALE_DIRECTORY) and (Zend_Locale::isLocale($file, true, false))) {
                        $options['locale'] = $file;
                        $prev              = (string) $options['locale'];
                    }
                } else if ($info->isFile()) {
                    // filename as locale
                    if ($options['scan'] === self::LOCALE_FILENAME) {
                        $filename = explode('.', $file);
                        array_pop($filename);
                        $filename = implode('.', $filename);
                        if (Zend_Locale::isLocale((string) $filename, true, false)) {
                            $options['locale'] = (string) $filename;
                        } else {
                            $parts  = explode('.', $file);
                            $parts2 = array();
                            foreach($parts as $token) {
                                $parts2 += explode('_', $token);
                            }
                            $parts  = array_merge($parts, $parts2);
                            $parts2 = array();
                            foreach($parts as $token) {
                                $parts2 += explode('-', $token);
                            }
                            $parts = array_merge($parts, $parts2);
                            $parts = array_unique($parts);
                            $prev  = '';
                            foreach($parts as $token) {
                                if (Zend_Locale::isLocale($token, true, false)) {
                                    if (strlen($prev) <= strlen($token)) {
                                        $options['locale'] = $token;
                                        $prev              = $token;
                                    }
                                }
                            }
                        }
                    }

                    try {
                        $options['content'] = $info->getPathname();
                        $this->_addTranslationData($options);
                    } catch (Zend_Translate_Exception $e) {
                        // ignore failed sources while scanning
                    }
                }
            }

            unset($iterator);
        } else {
            $this->_addTranslationData($options);
        }

        if ((isset($this->_translate[$originate]) === true) and (count($this->_translate[$originate]) > 0)) {
            $this->setLocale($originate);
        }

        return $this;
    }

    /**
     * Sets new adapter options
     *
     * @param  array $options Adapter options
     * @throws Zend_Translate_Exception
     * @return Zend_Translate_Adapter Provides fluent interface
     */
    public function setOptions(array $options = array())
    {
        $change = false;
        $locale = null;
        foreach ($options as $key => $option) {
            if ($key == 'locale') {
                $locale = $option;
            } else if ((isset($this->_options[$key]) and ($this->_options[$key] != $option)) or
                    !isset($this->_options[$key])) {
                if (($key == 'log') && !($option instanceof Zend_Log)) {
                    #require_once 'Zend/Translate/Exception.php';
                    throw new Zend_Translate_Exception('Instance of Zend_Log expected for option log');
                }

                if ($key == 'cache') {
                    self::setCache($option);
                    continue;
                }

                $this->_options[$key] = $option;
                $change = true;
            }
        }

        if ($locale !== null) {
            $this->setLocale($locale);
        }

        if (isset(self::$_cache) and ($change == true)) {
            $id = 'Zend_Translate_' . $this->toString() . '_Options';
            if (self::$_cacheTags) {
                self::$_cache->save($this->_options, $id, array($this->_options['tag']));
            } else {
                self::$_cache->save($this->_options, $id);
            }
        }

        return $this;
    }

    /**
     * Returns the adapters name and it's options
     *
     * @param  string|null $optionKey String returns this option
     *                                null returns all options
     * @return integer|string|array|null
     */
    public function getOptions($optionKey = null)
    {
        if ($optionKey === null) {
            return $this->_options;
        }

        if (isset($this->_options[$optionKey]) === true) {
            return $this->_options[$optionKey];
        }

        return null;
    }

    /**
     * Gets locale
     *
     * @return Zend_Locale|string|null
     */
    public function getLocale()
    {
        return $this->_options['locale'];
    }

    /**
     * Sets locale
     *
     * @param  string|Zend_Locale $locale Locale to set
     * @throws Zend_Translate_Exception
     * @return Zend_Translate_Adapter Provides fluent interface
     */
    public function setLocale($locale)
    {
        if (($locale === "auto") or ($locale === null)) {
            $this->_automatic = true;
        } else {
            $this->_automatic = false;
        }

        try {
            $locale = Zend_Locale::findLocale($locale);
        } catch (Zend_Locale_Exception $e) {
            #require_once 'Zend/Translate/Exception.php';
            throw new Zend_Translate_Exception("The given Language ({$locale}) does not exist", 0, $e);
        }

        if (!isset($this->_translate[$locale])) {
            $temp = explode('_', $locale);
            if (!isset($this->_translate[$temp[0]]) and !isset($this->_translate[$locale])) {
                if (!$this->_options['disableNotices']) {
                    if ($this->_options['log']) {
                        $this->_options['log']->log("The language '{$locale}' has to be added before it can be used.", $this->_options['logPriority']);
                    } else {
                        trigger_error("The language '{$locale}' has to be added before it can be used.", E_USER_NOTICE);
                    }
                }
            }

            $locale = $temp[0];
        }

        if (empty($this->_translate[$locale])) {
            if (!$this->_options['disableNotices']) {
                if ($this->_options['log']) {
                    $this->_options['log']->log("No translation for the language '{$locale}' available.", $this->_options['logPriority']);
                } else {
                    trigger_error("No translation for the language '{$locale}' available.", E_USER_NOTICE);
                }
            }
        }

        if ($this->_options['locale'] != $locale) {
            $this->_options['locale'] = $locale;

            if (isset(self::$_cache)) {
                $id = 'Zend_Translate_' . $this->toString() . '_Options';
                if (self::$_cacheTags) {
                    self::$_cache->save($this->_options, $id, array($this->_options['tag']));
                } else {
                    self::$_cache->save($this->_options, $id);
                }
            }
        }

        return $this;
    }

    /**
     * Returns the available languages from this adapter
     *
     * @return array|null
     */
    public function getList()
    {
        $list = array_keys($this->_translate);
        $result = null;
        foreach($list as $value) {
            if (!empty($this->_translate[$value])) {
                $result[$value] = $value;
            }
        }
        return $result;
    }

    /**
     * Returns the message id for a given translation
     * If no locale is given, the actual language will be used
     *
     * @param  string             $message Message to get the key for
     * @param  string|Zend_Locale $locale (optional) Language to return the message ids from
     * @return string|array|false
     */
    public function getMessageId($message, $locale = null)
    {
        if (empty($locale) or !$this->isAvailable($locale)) {
            $locale = $this->_options['locale'];
        }

        return array_search($message, $this->_translate[(string) $locale]);
    }

    /**
     * Returns all available message ids from this adapter
     * If no locale is given, the actual language will be used
     *
     * @param  string|Zend_Locale $locale (optional) Language to return the message ids from
     * @return array
     */
    public function getMessageIds($locale = null)
    {
        if (empty($locale) or !$this->isAvailable($locale)) {
            $locale = $this->_options['locale'];
        }

        return array_keys($this->_translate[(string) $locale]);
    }

    /**
     * Returns all available translations from this adapter
     * If no locale is given, the actual language will be used
     * If 'all' is given the complete translation dictionary will be returned
     *
     * @param  string|Zend_Locale $locale (optional) Language to return the messages from
     * @return array
     */
    public function getMessages($locale = null)
    {
        if ($locale === 'all') {
            return $this->_translate;
        }

        if ((empty($locale) === true) or ($this->isAvailable($locale) === false)) {
            $locale = $this->_options['locale'];
        }

        return $this->_translate[(string) $locale];
    }

    /**
     * Is the wished language available ?
     *
     * @see    Zend_Locale
     * @param  string|Zend_Locale $locale Language to search for, identical with locale identifier,
     *                                    @see Zend_Locale for more information
     * @return boolean
     */
    public function isAvailable($locale)
    {
        $return = isset($this->_translate[(string) $locale]);
        return $return;
    }

    /**
     * Load translation data
     *
     * @param  mixed              $data
     * @param  string|Zend_Locale $locale
     * @param  array              $options (optional)
     * @return array
     */
    abstract protected function _loadTranslationData($data, $locale, array $options = array());

    /**
     * Internal function for adding translation data
     *
     * This may be a new language or additional data for an existing language
     * If the options 'clear' is true, then the translation data for the specified
     * language is replaced and added otherwise
     *
     * @see    Zend_Locale
     * @param  array|Zend_Config $content Translation data to add
     * @throws Zend_Translate_Exception
     * @return Zend_Translate_Adapter Provides fluent interface
     */
    private function _addTranslationData($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (func_num_args() > 1) {
            $args = func_get_args();
            $options['content'] = array_shift($args);

            if (!empty($args)) {
                $options['locale'] = array_shift($args);
            }

            if (!empty($args)) {
                $options += array_shift($args);
            }
        }

        if (($options['content'] instanceof Zend_Translate) || ($options['content'] instanceof Zend_Translate_Adapter)) {
            $options['usetranslateadapter'] = true;
            if (!empty($options['locale']) && ($options['locale'] !== 'auto')) {
                $options['content'] = $options['content']->getMessages($options['locale']);
            } else {
                $content = $options['content'];
                $locales = $content->getList();
                foreach ($locales as $locale) {
                    $options['locale']  = $locale;
                    $options['content'] = $content->getMessages($locale);
                    $this->_addTranslationData($options);
                }

                return $this;
            }
        }

        try {
            $options['locale'] = Zend_Locale::findLocale($options['locale']);
        } catch (Zend_Locale_Exception $e) {
            #require_once 'Zend/Translate/Exception.php';
            throw new Zend_Translate_Exception("The given Language '{$options['locale']}' does not exist", 0, $e);
        }

        if ($options['clear'] || !isset($this->_translate[$options['locale']])) {
            $this->_translate[$options['locale']] = array();
        }

        $read = true;
        if (isset(self::$_cache)) {
            $id = 'Zend_Translate_' . md5(serialize($options['content'])) . '_' . $this->toString();
            $temp = self::$_cache->load($id);
            if ($temp) {
                $read = false;
            }
        }

        if ($options['reload']) {
            $read = true;
        }

        if ($read) {
            if (!empty($options['usetranslateadapter'])) {
                $temp = array($options['locale'] => $options['content']);
            } else {
                $temp = $this->_loadTranslationData($options['content'], $options['locale'], $options);
            }
        }

        if (empty($temp)) {
            $temp = array();
        }

        $keys = array_keys($temp);
        foreach($keys as $key) {
            if (!isset($this->_translate[$key])) {
                $this->_translate[$key] = array();
            }

            if (array_key_exists($key, $temp) && is_array($temp[$key])) {
                $this->_translate[$key] = $temp[$key] + $this->_translate[$key];
            }
        }

        if ($this->_automatic === true) {
            $find = new Zend_Locale($options['locale']);
            $browser = $find->getEnvironment() + $find->getBrowser();
            arsort($browser);
            foreach($browser as $language => $quality) {
                if (isset($this->_translate[$language])) {
                    $this->_options['locale'] = $language;
                    break;
                }
            }
        }

        if (($read) and (isset(self::$_cache))) {
            $id = 'Zend_Translate_' . md5(serialize($options['content'])) . '_' . $this->toString();
            if (self::$_cacheTags) {
                self::$_cache->save($temp, $id, array($this->_options['tag']));
            } else {
                self::$_cache->save($temp, $id);
            }
        }

        return $this;
    }

    /**
     * Translates the given string
     * returns the translation
     *
     * @see Zend_Locale
     * @param  string|array       $messageId Translation string, or Array for plural translations
     * @param  string|Zend_Locale $locale    (optional) Locale/Language to use, identical with
     *                                       locale identifier, @see Zend_Locale for more information
     * @return string
     */
    public function translate($messageId, $locale = null)
    {
        if ($locale === null) {
            $locale = $this->_options['locale'];
        }

        $plural = null;
        if (is_array($messageId)) {
            if (count($messageId) > 2) {
                $number = array_pop($messageId);
                if (!is_numeric($number)) {
                    $plocale = $number;
                    $number  = array_pop($messageId);
                } else {
                    $plocale = 'en';
                }

                $plural    = $messageId;
                $messageId = $messageId[0];
            } else {
                $messageId = $messageId[0];
            }
        }

        if (!Zend_Locale::isLocale($locale, true, false)) {
            if (!Zend_Locale::isLocale($locale, false, false)) {
                // language does not exist, return original string
                $this->_log($messageId, $locale);
                // use rerouting when enabled
                if (!empty($this->_options['route'])) {
                    if (array_key_exists($locale, $this->_options['route']) &&
                        !array_key_exists($locale, $this->_routed)) {
                        $this->_routed[$locale] = true;
                        return $this->translate($messageId, $this->_options['route'][$locale]);
                    }
                }

                $this->_routed = array();
                if ($plural === null) {
                    return $messageId;
                }

                $rule = Zend_Translate_Plural::getPlural($number, $plocale);
                if (!isset($plural[$rule])) {
                    $rule = 0;
                }

                return $plural[$rule];
            }

            $locale = new Zend_Locale($locale);
        }

        $locale = (string) $locale;
        if ((is_string($messageId) || is_int($messageId)) && isset($this->_translate[$locale][$messageId])) {
            // return original translation
            if ($plural === null) {
                $this->_routed = array();
                return $this->_translate[$locale][$messageId];
            }

            $rule = Zend_Translate_Plural::getPlural($number, $locale);
            if (isset($this->_translate[$locale][$plural[0]][$rule])) {
                $this->_routed = array();
                return $this->_translate[$locale][$plural[0]][$rule];
            }
        } else if (strlen($locale) != 2) {
            // faster than creating a new locale and separate the leading part
            $locale = substr($locale, 0, -strlen(strrchr($locale, '_')));

            if ((is_string($messageId) || is_int($messageId)) && isset($this->_translate[$locale][$messageId])) {
                // return regionless translation (en_US -> en)
                if ($plural === null) {
                    $this->_routed = array();
                    return $this->_translate[$locale][$messageId];
                }

                $rule = Zend_Translate_Plural::getPlural($number, $locale);
                if (isset($this->_translate[$locale][$plural[0]][$rule])) {
                    $this->_routed = array();
                    return $this->_translate[$locale][$plural[0]][$rule];
                }
            }
        }

        $this->_log($messageId, $locale);
        // use rerouting when enabled
        if (!empty($this->_options['route'])) {
            if (array_key_exists($locale, $this->_options['route']) &&
                !array_key_exists($locale, $this->_routed)) {
                $this->_routed[$locale] = true;
                return $this->translate($messageId, $this->_options['route'][$locale]);
            }
        }

        $this->_routed = array();
        if ($plural === null) {
            return $messageId;
        }

        $rule = Zend_Translate_Plural::getPlural($number, $plocale);
        if (!isset($plural[$rule])) {
            $rule = 0;
        }

        return $plural[$rule];
    }

    /**
     * Translates the given string using plural notations
     * Returns the translated string
     *
     * @see Zend_Locale
     * @param  string             $singular Singular translation string
     * @param  string             $plural   Plural translation string
     * @param  integer            $number   Number for detecting the correct plural
     * @param  string|Zend_Locale $locale   (Optional) Locale/Language to use, identical with
     *                                      locale identifier, @see Zend_Locale for more information
     * @return string
     */
    public function plural($singular, $plural, $number, $locale = null)
    {
        return $this->translate(array($singular, $plural, $number), $locale);
    }

    /**
     * Logs a message when the log option is set
     *
     * @param string $message Message to log
     * @param String $locale  Locale to log
     */
    protected function _log($message, $locale) {
        if ($this->_options['logUntranslated']) {
            $message = str_replace('%message%', $message, $this->_options['logMessage']);
            $message = str_replace('%locale%', $locale, $message);
            if ($this->_options['log']) {
                $this->_options['log']->log($message, $this->_options['logPriority']);
            } else {
                trigger_error($message, E_USER_NOTICE);
            }
        }
    }

    /**
     * Translates the given string
     * returns the translation
     *
     * @param  string             $messageId Translation string
     * @param  string|Zend_Locale $locale    (optional) Locale/Language to use, identical with locale
     *                                       identifier, @see Zend_Locale for more information
     * @return string
     */
    public function _($messageId, $locale = null)
    {
        return $this->translate($messageId, $locale);
    }

    /**
     * Checks if a string is translated within the source or not
     * returns boolean
     *
     * @param  string             $messageId Translation string
     * @param  boolean            $original  (optional) Allow translation only for original language
     *                                       when true, a translation for 'en_US' would give false when it can
     *                                       be translated with 'en' only
     * @param  string|Zend_Locale $locale    (optional) Locale/Language to use, identical with locale identifier,
     *                                       see Zend_Locale for more information
     * @return boolean
     */
    public function isTranslated($messageId, $original = false, $locale = null)
    {
        if (($original !== false) and ($original !== true)) {
            $locale   = $original;
            $original = false;
        }

        if ($locale === null) {
            $locale = $this->_options['locale'];
        }

        if (!Zend_Locale::isLocale($locale, true, false)) {
            if (!Zend_Locale::isLocale($locale, false, false)) {
                // language does not exist, return original string
                return false;
            }

            $locale = new Zend_Locale($locale);
        }

        $locale = (string) $locale;
        if ((is_string($messageId) || is_int($messageId)) && isset($this->_translate[$locale][$messageId])) {
            // return original translation
            return true;
        } else if ((strlen($locale) != 2) and ($original === false)) {
            // faster than creating a new locale and separate the leading part
            $locale = substr($locale, 0, -strlen(strrchr($locale, '_')));

            if ((is_string($messageId) || is_int($messageId)) && isset($this->_translate[$locale][$messageId])) {
                // return regionless translation (en_US -> en)
                return true;
            }
        }

        // No translation found, return original
        return false;
    }

    /**
     * Returns the set cache
     *
     * @return Zend_Cache_Core The set cache
     */
    public static function getCache()
    {
        return self::$_cache;
    }

    /**
     * Sets a cache for all Zend_Translate_Adapters
     *
     * @param Zend_Cache_Core $cache Cache to store to
     */
    public static function setCache(Zend_Cache_Core $cache)
    {
        self::$_cache = $cache;
        self::_getTagSupportForCache();
    }

    /**
     * Returns true when a cache is set
     *
     * @return boolean
     */
    public static function hasCache()
    {
        if (self::$_cache !== null) {
            return true;
        }

        return false;
    }

    /**
     * Removes any set cache
     *
     * @return void
     */
    public static function removeCache()
    {
        self::$_cache = null;
    }

    /**
     * Clears all set cache data
     *
     * @param string $tag Tag to clear when the default tag name is not used
     * @return void
     */
    public static function clearCache($tag = null)
    {
        #require_once 'Zend/Cache.php';
        if (self::$_cacheTags) {
            if ($tag == null) {
                $tag = 'Zend_Translate';
            }

            self::$_cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($tag));
        } else {
            self::$_cache->clean(Zend_Cache::CLEANING_MODE_ALL);
        }
    }

    /**
     * Returns the adapter name
     *
     * @return string
     */
    abstract public function toString();

    /**
     * Internal method to check if the given cache supports tags
     *
     * @param Zend_Cache $cache
     */
    private static function _getTagSupportForCache()
    {
        $backend = self::$_cache->getBackend();
        if ($backend instanceof Zend_Cache_Backend_ExtendedInterface) {
            $cacheOptions = $backend->getCapabilities();
            self::$_cacheTags = $cacheOptions['tags'];
        } else {
            self::$_cacheTags = false;
        }

        return self::$_cacheTags;
    }
}
