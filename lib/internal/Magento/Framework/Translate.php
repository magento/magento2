<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Translate library
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Translate implements \Magento\Framework\TranslateInterface
{
    /**
     * Locale code
     *
     * @var string
     */
    protected $_localeCode;

    /**
     * Translator configuration array
     *
     * @var array
     */
    protected $_config;

    /**
     * Cache identifier
     *
     * @var string
     */
    protected $_cacheId;

    /**
     * Translation data
     *
     * @var []
     */
    protected $_data = [];

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $_viewDesign;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface $cache
     */
    protected $_cache;

    /**
     * @var \Magento\Framework\View\FileSystem
     */
    protected $_viewFileSystem;

    /**
     * @var \Magento\Framework\Module\ModuleList
     */
    protected $_moduleList;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $_modulesReader;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     */
    protected $_scopeResolver;

    /**
     * @var \Magento\Framework\Translate\ResourceInterface
     */
    protected $_translateResource;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_locale;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Read
     */
    protected $directory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $_csvParser;

    /**
     * @var \Magento\Framework\App\Language\Dictionary
     */
    protected $packDictionary;

    /**
     * @param \Magento\Framework\View\DesignInterface $viewDesign
     * @param \Magento\Framework\Cache\FrontendInterface $cache
     * @param \Magento\Framework\View\FileSystem $viewFileSystem
     * @param \Magento\Framework\Module\ModuleList $moduleList
     * @param \Magento\Framework\Module\Dir\Reader $modulesReader
     * @param \Magento\Framework\App\ScopeResolverInterface $scopeResolver
     * @param \Magento\Framework\Translate\ResourceInterface $translate
     * @param \Magento\Framework\Locale\ResolverInterface $locale
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\File\Csv $csvParser
     * @param \Magento\Framework\App\Language\Dictionary $packDictionary
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\DesignInterface $viewDesign,
        \Magento\Framework\Cache\FrontendInterface $cache,
        \Magento\Framework\View\FileSystem $viewFileSystem,
        \Magento\Framework\Module\ModuleList $moduleList,
        \Magento\Framework\Module\Dir\Reader $modulesReader,
        \Magento\Framework\App\ScopeResolverInterface $scopeResolver,
        \Magento\Framework\Translate\ResourceInterface $translate,
        \Magento\Framework\Locale\ResolverInterface $locale,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\File\Csv $csvParser,
        \Magento\Framework\App\Language\Dictionary $packDictionary
    ) {
        $this->_viewDesign = $viewDesign;
        $this->_cache = $cache;
        $this->_viewFileSystem = $viewFileSystem;
        $this->_moduleList = $moduleList;
        $this->_modulesReader = $modulesReader;
        $this->_scopeResolver = $scopeResolver;
        $this->_translateResource = $translate;
        $this->_locale = $locale;
        $this->_appState = $appState;
        $this->request = $request;
        $this->directory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->_csvParser = $csvParser;
        $this->packDictionary = $packDictionary;
    }

    /**
     * Initialize translation data
     *
     * @param string|null $area
     * @param bool $forceReload
     * @return $this
     */
    public function loadData($area = null, $forceReload = false)
    {
        $this->setConfig(
            ['area' => isset($area) ? $area : $this->_appState->getAreaCode()]
        );

        if (!$forceReload) {
            $this->_data = $this->_loadCache();
            if ($this->_data !== false) {
                return $this;
            }
        }
        $this->_data = [];

        $this->_loadModuleTranslation();
        $this->_loadThemeTranslation();
        $this->_loadPackTranslation();
        $this->_loadDbTranslation();

        $this->_saveCache();

        return $this;
    }

    /**
     * Initialize configuration
     *
     * @param   array $config
     * @return  $this
     */
    protected function setConfig($config)
    {
        $this->_config = $config;
        if (!isset($this->_config['locale'])) {
            $this->_config['locale'] = $this->getLocale();
        }
        if (!isset($this->_config['scope'])) {
            $this->_config['scope'] = $this->getScope();
        }
        if (!isset($this->_config['theme'])) {
            $this->_config['theme'] = $this->_viewDesign->getDesignTheme()->getId();
        }
        return $this;
    }

    /**
     * Retrieve scope code
     *
     * @return string
     */
    protected function getScope()
    {
        $scope = ($this->getConfig('area') == 'adminhtml') ? 'admin' : null;
        return $this->_scopeResolver->getScope($scope)->getCode();
    }

    /**
     * Retrieve config value by key
     *
     * @param   string $key
     * @return  mixed
     */
    protected function getConfig($key)
    {
        if (isset($this->_config[$key])) {
            return $this->_config[$key];
        }
        return null;
    }

    /**
     * Load data from module translation files
     *
     * @return $this
     */
    protected function _loadModuleTranslation()
    {
        foreach ($this->_moduleList->getNames() as $module) {
            $moduleFilePath = $this->_getModuleTranslationFile($module, $this->getLocale());
            $this->_addData($this->_getFileData($moduleFilePath));
        }
        return $this;
    }

    /**
     * Adding translation data
     *
     * @param array $data
     * @return $this
     */
    protected function _addData($data)
    {
        foreach ($data as $key => $value) {
            if ($key === $value) {
                continue;
            }

            $key = str_replace('""', '"', $key);
            $value  = str_replace('""', '"', $value);

            $this->_data[$key] = $value;
        }
        return $this;
    }

    /**
     * Load current theme translation
     *
     * @return $this
     */
    protected function _loadThemeTranslation()
    {
        if (!$this->_config['theme']) {
            return $this;
        }

        $file = $this->_getThemeTranslationFile($this->getLocale());
        if ($file) {
            $this->_addData($this->_getFileData($file));
        }
        return $this;
    }

    /**
     * Load translation dictionary from language packages
     *
     * @return void
     */
    protected function _loadPackTranslation()
    {
        $data = $this->packDictionary->getDictionary($this->getLocale());
        $this->_addData($data);
    }

    /**
     * Loading current translation from DB
     *
     * @return $this
     */
    protected function _loadDbTranslation()
    {
        $data = $this->_translateResource->getTranslationArray(null, $this->getLocale());
        $this->_addData($data);
        return $this;
    }

    /**
     * Retrieve translation file for module
     *
     * @param string $moduleName
     * @param string $locale
     * @return string
     */
    protected function _getModuleTranslationFile($moduleName, $locale)
    {
        $file = $this->_modulesReader->getModuleDir('i18n', $moduleName);
        $file .= '/' . $locale . '.csv';
        return $file;
    }

    /**
     * Retrieve translation file for theme
     *
     * @param string $locale
     * @return string
     */
    protected function _getThemeTranslationFile($locale)
    {
        return $this->_viewFileSystem->getLocaleFileName(
            'i18n' . '/' . $locale . '.csv',
            ['area' => $this->getConfig('area')]
        );
    }

    /**
     * Retrieve data from file
     *
     * @param string $file
     * @return array
     */
    protected function _getFileData($file)
    {
        $data = [];
        if ($this->directory->isExist($this->directory->getRelativePath($file))) {
            $this->_csvParser->setDelimiter(',');
            $data = $this->_csvParser->getDataPairs($file);
        }
        return $data;
    }

    /**
     * Retrieve translation data
     *
     * @return array
     */
    public function getData()
    {
        if (is_null($this->_data)) {
            return [];
        }
        return $this->_data;
    }

    /**
     * Retrieve locale
     *
     * @return string
     */
    public function getLocale()
    {
        if (null === $this->_localeCode) {
            $this->_localeCode = $this->_locale->getLocaleCode();
        }
        return $this->_localeCode;
    }

    /**
     * Set locale
     *
     * @param string $locale
     * @return \Magento\Framework\TranslateInterface
     */
    public function setLocale($locale)
    {
        $this->_localeCode = $locale;
        return $this;
    }

    /**
     * Retrieve theme code
     *
     * @return string
     */
    public function getTheme()
    {
        $theme = $this->request->getParam('theme');
        if (empty($theme)) {
            return 'theme' . $this->getConfig('theme');
        }
        return 'theme' . $theme['theme_title'];
    }

    /**
     * Retrieve cache identifier
     *
     * @param bool $forceReload
     * @return string
     */
    protected function getCacheId($forceReload = false)
    {
        if ($this->_cacheId === null || $forceReload) {
            $this->_cacheId = \Magento\Framework\App\Cache\Type\Translate::TYPE_IDENTIFIER;
            if (isset($this->_config['locale'])) {
                $this->_cacheId .= '_' . $this->_config['locale'];
            }
            if (isset($this->_config['area'])) {
                $this->_cacheId .= '_' . $this->_config['area'];
            }
            if (isset($this->_config['scope'])) {
                $this->_cacheId .= '_' . $this->_config['scope'];
            }
            if (isset($this->_config['theme'])) {
                $this->_cacheId .= '_' . $this->_config['theme'];
            }
        }
        return $this->_cacheId;
    }

    /**
     * Loading data cache
     *
     * @return array|bool
     */
    protected function _loadCache()
    {
        $data = $this->_cache->load($this->getCacheId());
        if ($data) {
            $data = unserialize($data);
        }
        return $data;
    }

    /**
     * Saving data cache
     *
     * @return $this
     */
    protected function _saveCache()
    {
        $this->_cache->save(serialize($this->getData()), $this->getCacheId(true), [], false);
        return $this;
    }
}
