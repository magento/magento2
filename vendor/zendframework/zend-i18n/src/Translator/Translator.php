<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\I18n\Translator;

use Locale;
use Traversable;
use Zend\Cache;
use Zend\Cache\Storage\StorageInterface as CacheStorage;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\I18n\Exception;
use Zend\I18n\Translator\Loader\FileLoaderInterface;
use Zend\I18n\Translator\Loader\RemoteLoaderInterface;
use Zend\Stdlib\ArrayUtils;

/**
 * Translator.
 */
class Translator implements TranslatorInterface
{
    /**
     * Event fired when the translation for a message is missing.
     */
    const EVENT_MISSING_TRANSLATION = 'missingTranslation';

    /**
     * Event fired when no messages were loaded for a locale/text-domain combination.
     */
    const EVENT_NO_MESSAGES_LOADED = 'noMessagesLoaded';

    /**
     * Messages loaded by the translator.
     *
     * @var array
     */
    protected $messages = array();

    /**
     * Files used for loading messages.
     *
     * @var array
     */
    protected $files = array();

    /**
     * Patterns used for loading messages.
     *
     * @var array
     */
    protected $patterns = array();

    /**
     * Remote locations for loading messages.
     *
     * @var array
     */
    protected $remote = array();

    /**
     * Default locale.
     *
     * @var string
     */
    protected $locale;

    /**
     * Locale to use as fallback if there is no translation.
     *
     * @var string
     */
    protected $fallbackLocale;

    /**
     * Translation cache.
     *
     * @var CacheStorage
     */
    protected $cache;

    /**
     * Plugin manager for translation loaders.
     *
     * @var LoaderPluginManager
     */
    protected $pluginManager;

    /**
     * Event manager for triggering translator events.
     *
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * Whether events are enabled
     *
     * @var bool
     */
    protected $eventsEnabled = false;

    /**
     * Instantiate a translator
     *
     * @param  array|Traversable                  $options
     * @return Translator
     * @throws Exception\InvalidArgumentException
     */
    public static function factory($options)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (!is_array($options)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an array or Traversable object; received "%s"',
                __METHOD__,
                (is_object($options) ? get_class($options) : gettype($options))
            ));
        }

        $translator = new static();

        // locales
        if (isset($options['locale'])) {
            $locales = (array) $options['locale'];
            $translator->setLocale(array_shift($locales));
            if (count($locales) > 0) {
                $translator->setFallbackLocale(array_shift($locales));
            }
        }

        // file patterns
        if (isset($options['translation_file_patterns'])) {
            if (!is_array($options['translation_file_patterns'])) {
                throw new Exception\InvalidArgumentException(
                    '"translation_file_patterns" should be an array'
                );
            }

            $requiredKeys = array('type', 'base_dir', 'pattern');
            foreach ($options['translation_file_patterns'] as $pattern) {
                foreach ($requiredKeys as $key) {
                    if (!isset($pattern[$key])) {
                        throw new Exception\InvalidArgumentException(
                            "'{$key}' is missing for translation pattern options"
                        );
                    }
                }

                $translator->addTranslationFilePattern(
                    $pattern['type'],
                    $pattern['base_dir'],
                    $pattern['pattern'],
                    isset($pattern['text_domain']) ? $pattern['text_domain'] : 'default'
                );
            }
        }

        // files
        if (isset($options['translation_files'])) {
            if (!is_array($options['translation_files'])) {
                throw new Exception\InvalidArgumentException(
                    '"translation_files" should be an array'
                );
            }

            $requiredKeys = array('type', 'filename');
            foreach ($options['translation_files'] as $file) {
                foreach ($requiredKeys as $key) {
                    if (!isset($file[$key])) {
                        throw new Exception\InvalidArgumentException(
                            "'{$key}' is missing for translation file options"
                        );
                    }
                }

                $translator->addTranslationFile(
                    $file['type'],
                    $file['filename'],
                    isset($file['text_domain']) ? $file['text_domain'] : 'default',
                    isset($file['locale']) ? $file['locale'] : null
                );
            }
        }

        // remote
        if (isset($options['remote_translation'])) {
            if (!is_array($options['remote_translation'])) {
                throw new Exception\InvalidArgumentException(
                    '"remote_translation" should be an array'
                );
            }

            $requiredKeys = array('type');
            foreach ($options['remote_translation'] as $remote) {
                foreach ($requiredKeys as $key) {
                    if (!isset($remote[$key])) {
                        throw new Exception\InvalidArgumentException(
                            "'{$key}' is missing for remote translation options"
                        );
                    }
                }

                $translator->addRemoteTranslations(
                    $remote['type'],
                    isset($remote['text_domain']) ? $remote['text_domain'] : 'default'
                );
            }
        }

        // cache
        if (isset($options['cache'])) {
            if ($options['cache'] instanceof CacheStorage) {
                $translator->setCache($options['cache']);
            } else {
                $translator->setCache(Cache\StorageFactory::factory($options['cache']));
            }
        }

        // event manager enabled
        if (isset($options['event_manager_enabled']) && $options['event_manager_enabled']) {
            $translator->enableEventManager();
        }

        return $translator;
    }

    /**
     * Set the default locale.
     *
     * @param  string     $locale
     * @return Translator
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get the default locale.
     *
     * @return string
     * @throws Exception\ExtensionNotLoadedException if ext/intl is not present and no locale set
     */
    public function getLocale()
    {
        if ($this->locale === null) {
            if (!extension_loaded('intl')) {
                throw new Exception\ExtensionNotLoadedException(sprintf(
                    '%s component requires the intl PHP extension',
                    __NAMESPACE__
                ));
            }
            $this->locale = Locale::getDefault();
        }

        return $this->locale;
    }

    /**
     * Set the fallback locale.
     *
     * @param  string     $locale
     * @return Translator
     */
    public function setFallbackLocale($locale)
    {
        $this->fallbackLocale = $locale;

        return $this;
    }

    /**
     * Get the fallback locale.
     *
     * @return string
     */
    public function getFallbackLocale()
    {
        return $this->fallbackLocale;
    }

    /**
     * Sets a cache
     *
     * @param  CacheStorage $cache
     * @return Translator
     */
    public function setCache(CacheStorage $cache = null)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Returns the set cache
     *
     * @return CacheStorage The set cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Set the plugin manager for translation loaders
     *
     * @param  LoaderPluginManager $pluginManager
     * @return Translator
     */
    public function setPluginManager(LoaderPluginManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;

        return $this;
    }

    /**
     * Retrieve the plugin manager for translation loaders.
     *
     * Lazy loads an instance if none currently set.
     *
     * @return LoaderPluginManager
     */
    public function getPluginManager()
    {
        if (!$this->pluginManager instanceof LoaderPluginManager) {
            $this->setPluginManager(new LoaderPluginManager());
        }

        return $this->pluginManager;
    }

    /**
     * Translate a message.
     *
     * @param  string $message
     * @param  string $textDomain
     * @param  string $locale
     * @return string
     */
    public function translate($message, $textDomain = 'default', $locale = null)
    {
        $locale      = ($locale ?: $this->getLocale());
        $translation = $this->getTranslatedMessage($message, $locale, $textDomain);

        if ($translation !== null && $translation !== '') {
            return $translation;
        }

        if (null !== ($fallbackLocale = $this->getFallbackLocale())
            && $locale !== $fallbackLocale
        ) {
            return $this->translate($message, $textDomain, $fallbackLocale);
        }

        return $message;
    }

    /**
     * Translate a plural message.
     *
     * @param  string                         $singular
     * @param  string                         $plural
     * @param  int                            $number
     * @param  string                         $textDomain
     * @param  string|null                    $locale
     * @return string
     * @throws Exception\OutOfBoundsException
     */
    public function translatePlural(
        $singular,
        $plural,
        $number,
        $textDomain = 'default',
        $locale = null
    ) {
        $locale      = $locale ?: $this->getLocale();
        $translation = $this->getTranslatedMessage($singular, $locale, $textDomain);

        if ($translation === null || $translation === '') {
            if (null !== ($fallbackLocale = $this->getFallbackLocale())
                && $locale !== $fallbackLocale
            ) {
                return $this->translatePlural(
                    $singular,
                    $plural,
                    $number,
                    $textDomain,
                    $fallbackLocale
                );
            }

            return ($number == 1 ? $singular : $plural);
        } elseif (is_string($translation)) {
            $translation = array($translation);
        }

        $index = $this->messages[$textDomain][$locale]
                      ->getPluralRule()
                      ->evaluate($number);

        if (!isset($translation[$index])) {
            throw new Exception\OutOfBoundsException(
                sprintf('Provided index %d does not exist in plural array', $index)
            );
        }

        return $translation[$index];
    }

    /**
     * Get a translated message.
     *
     * @triggers getTranslatedMessage.missing-translation
     * @param    string $message
     * @param    string $locale
     * @param    string $textDomain
     * @return   string|null
     */
    protected function getTranslatedMessage(
        $message,
        $locale,
        $textDomain = 'default'
    ) {
        if ($message === '') {
            return '';
        }

        if (!isset($this->messages[$textDomain][$locale])) {
            $this->loadMessages($textDomain, $locale);
        }

        if (isset($this->messages[$textDomain][$locale][$message])) {
            return $this->messages[$textDomain][$locale][$message];
        }

        if ($this->isEventManagerEnabled()) {
            $results = $this->getEventManager()->trigger(
                self::EVENT_MISSING_TRANSLATION,
                $this,
                array(
                    'message'     => $message,
                    'locale'      => $locale,
                    'text_domain' => $textDomain,
                ),
                function ($r) {
                    return is_string($r);
                }
            );
            $last = $results->last();
            if (is_string($last)) {
                return $last;
            }
        }

        return;
    }

    /**
     * Add a translation file.
     *
     * @param  string     $type
     * @param  string     $filename
     * @param  string     $textDomain
     * @param  string     $locale
     * @return Translator
     */
    public function addTranslationFile(
        $type,
        $filename,
        $textDomain = 'default',
        $locale = null
    ) {
        $locale = $locale ?: '*';

        if (!isset($this->files[$textDomain])) {
            $this->files[$textDomain] = array();
        }

        $this->files[$textDomain][$locale][] = array(
            'type' => $type,
            'filename' => $filename,
        );

        return $this;
    }

    /**
     * Add multiple translations with a file pattern.
     *
     * @param  string     $type
     * @param  string     $baseDir
     * @param  string     $pattern
     * @param  string     $textDomain
     * @return Translator
     */
    public function addTranslationFilePattern(
        $type,
        $baseDir,
        $pattern,
        $textDomain = 'default'
    ) {
        if (!isset($this->patterns[$textDomain])) {
            $this->patterns[$textDomain] = array();
        }

        $this->patterns[$textDomain][] = array(
            'type'    => $type,
            'baseDir' => rtrim($baseDir, '/'),
            'pattern' => $pattern,
        );

        return $this;
    }

    /**
     * Add remote translations.
     *
     * @param  string     $type
     * @param  string     $textDomain
     * @return Translator
     */
    public function addRemoteTranslations($type, $textDomain = 'default')
    {
        if (!isset($this->remote[$textDomain])) {
            $this->remote[$textDomain] = array();
        }

        $this->remote[$textDomain][] = $type;

        return $this;
    }

    /**
     * Load messages for a given language and domain.
     *
     * @triggers loadMessages.no-messages-loaded
     * @param    string $textDomain
     * @param    string $locale
     * @throws   Exception\RuntimeException
     * @return   void
     */
    protected function loadMessages($textDomain, $locale)
    {
        if (!isset($this->messages[$textDomain])) {
            $this->messages[$textDomain] = array();
        }

        if (null !== ($cache = $this->getCache())) {
            $cacheId = 'Zend_I18n_Translator_Messages_' . md5($textDomain . $locale);

            if (null !== ($result = $cache->getItem($cacheId))) {
                $this->messages[$textDomain][$locale] = $result;

                return;
            }
        }

        $messagesLoaded  = false;
        $messagesLoaded |= $this->loadMessagesFromRemote($textDomain, $locale);
        $messagesLoaded |= $this->loadMessagesFromPatterns($textDomain, $locale);
        $messagesLoaded |= $this->loadMessagesFromFiles($textDomain, $locale);

        if (!$messagesLoaded) {
            $discoveredTextDomain = null;
            if ($this->isEventManagerEnabled()) {
                $results = $this->getEventManager()->trigger(
                    self::EVENT_NO_MESSAGES_LOADED,
                    $this,
                    array(
                        'locale'      => $locale,
                        'text_domain' => $textDomain,
                    ),
                    function ($r) {
                        return ($r instanceof TextDomain);
                    }
                );
                $last = $results->last();
                if ($last instanceof TextDomain) {
                    $discoveredTextDomain = $last;
                }
            }

            $this->messages[$textDomain][$locale] = $discoveredTextDomain;
            $messagesLoaded = true;
        }

        if ($messagesLoaded && $cache !== null) {
            $cache->setItem($cacheId, $this->messages[$textDomain][$locale]);
        }
    }

    /**
     * Load messages from remote sources.
     *
     * @param  string $textDomain
     * @param  string $locale
     * @return bool
     * @throws Exception\RuntimeException When specified loader is not a remote loader
     */
    protected function loadMessagesFromRemote($textDomain, $locale)
    {
        $messagesLoaded = false;

        if (isset($this->remote[$textDomain])) {
            foreach ($this->remote[$textDomain] as $loaderType) {
                $loader = $this->getPluginManager()->get($loaderType);

                if (!$loader instanceof RemoteLoaderInterface) {
                    throw new Exception\RuntimeException('Specified loader is not a remote loader');
                }

                if (isset($this->messages[$textDomain][$locale])) {
                    $this->messages[$textDomain][$locale]->merge($loader->load($locale, $textDomain));
                } else {
                    $this->messages[$textDomain][$locale] = $loader->load($locale, $textDomain);
                }

                $messagesLoaded = true;
            }
        }

        return $messagesLoaded;
    }

    /**
     * Load messages from patterns.
     *
     * @param  string $textDomain
     * @param  string $locale
     * @return bool
     * @throws Exception\RuntimeException When specified loader is not a file loader
     */
    protected function loadMessagesFromPatterns($textDomain, $locale)
    {
        $messagesLoaded = false;

        if (isset($this->patterns[$textDomain])) {
            foreach ($this->patterns[$textDomain] as $pattern) {
                $filename = $pattern['baseDir'] . '/' . sprintf($pattern['pattern'], $locale);

                if (is_file($filename)) {
                    $loader = $this->getPluginManager()->get($pattern['type']);

                    if (!$loader instanceof FileLoaderInterface) {
                        throw new Exception\RuntimeException('Specified loader is not a file loader');
                    }

                    if (isset($this->messages[$textDomain][$locale])) {
                        $this->messages[$textDomain][$locale]->merge($loader->load($locale, $filename));
                    } else {
                        $this->messages[$textDomain][$locale] = $loader->load($locale, $filename);
                    }

                    $messagesLoaded = true;
                }
            }
        }

        return $messagesLoaded;
    }

    /**
     * Load messages from files.
     *
     * @param  string $textDomain
     * @param  string $locale
     * @return bool
     * @throws Exception\RuntimeException When specified loader is not a file loader
     */
    protected function loadMessagesFromFiles($textDomain, $locale)
    {
        $messagesLoaded = false;

        foreach (array($locale, '*') as $currentLocale) {
            if (!isset($this->files[$textDomain][$currentLocale])) {
                continue;
            }

            foreach ($this->files[$textDomain][$currentLocale] as $file) {
                $loader = $this->getPluginManager()->get($file['type']);

                if (!$loader instanceof FileLoaderInterface) {
                    throw new Exception\RuntimeException('Specified loader is not a file loader');
                }

                if (isset($this->messages[$textDomain][$locale])) {
                    $this->messages[$textDomain][$locale]->merge($loader->load($locale, $file['filename']));
                } else {
                    $this->messages[$textDomain][$locale] = $loader->load($locale, $file['filename']);
                }

                $messagesLoaded = true;
            }

            unset($this->files[$textDomain][$currentLocale]);
        }

        return $messagesLoaded;
    }

    /**
     * Get the event manager.
     *
     * @return EventManagerInterface|null
     */
    public function getEventManager()
    {
        if (!$this->events instanceof EventManagerInterface) {
            $this->setEventManager(new EventManager());
        }

        return $this->events;
    }

    /**
     * Set the event manager instance used by this translator.
     *
     * @param  EventManagerInterface $events
     * @return Translator
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            __CLASS__,
            get_class($this),
            'translator',
        ));
        $this->events = $events;
        return $this;
    }

    /**
     * Check whether the event manager is enabled.
     *
     * @return boolean
     */
    public function isEventManagerEnabled()
    {
        return $this->eventsEnabled;
    }

    /**
     * Enable the event manager.
     *
     * @return Translator
     */
    public function enableEventManager()
    {
        $this->eventsEnabled = true;
        return $this;
    }

    /**
     * Disable the event manager.
     *
     * @return Translator
     */
    public function disableEventManager()
    {
        $this->eventsEnabled = false;
        return $this;
    }
}
