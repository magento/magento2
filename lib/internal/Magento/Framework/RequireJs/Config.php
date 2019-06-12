<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\RequireJs;

use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\ReadFactory;
use Magento\Framework\View\Asset\Minification;
use Magento\Framework\View\Asset\RepositoryMap;

/**
 * Provider of RequireJs config information
 */
class Config
{
    /**
     * Name of sub-directory where generated RequireJs config is placed
     *
     * @deprecated since 2.2.0 RequireJS Configuration file is moved into package directory
     */
    const DIR_NAME = '_requirejs';

    /**
     * File name of RequireJs config
     */
    const CONFIG_FILE_NAME = 'requirejs-config.js';

    /**
     * File name of RequireJs mixins
     */
    const MIXINS_FILE_NAME = 'mage/requirejs/mixins.js';

    /**
     * File name of RequireJs
     */
    const REQUIRE_JS_FILE_NAME = 'requirejs/require.js';

    /**
     * File name of StaticJs
     */
    const STATIC_FILE_NAME = 'mage/requirejs/static.js';

    /**
     * File name of minified files resolver
     */
    const MIN_RESOLVER_FILENAME = 'requirejs-min-resolver.js';

    /**
     * File name of RequireJs mixins
     */
    const MAP_FILE_NAME = 'requirejs-map.js';

    /**
     * File name of BaseUrlInterceptorJs
     */
    const URL_RESOLVER_FILE_NAME = 'mage/requirejs/baseUrlResolver.js';

    /**
     * File name of StaticJs
     */
    const BUNDLE_JS_DIR = 'js/bundle';

    /**
     * Template for combined RequireJs config file
     */
    const FULL_CONFIG_TEMPLATE = <<<config
(function(require){
%function%

%usages%
})(require);
config;

    /**
     * Template for wrapped partial config
     */
    const PARTIAL_CONFIG_TEMPLATE = <<<config
(function() {
%config%
require.config(config);
})();

config;

    /**
     * @var \Magento\Framework\RequireJs\Config\File\Collector\Aggregated
     */
    private $fileSource;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    private $design;

    /**
     * @var \Magento\Framework\Filesystem\File\ReadFactory
     */
    private $readFactory;

    /**
     * @var \Magento\Framework\View\Asset\ContextInterface
     */
    private $staticContext;

    /**
     * @var \Magento\Framework\Code\Minifier\AdapterInterface
     */
    private $minifyAdapter;

    /**
     * @var Minification
     */
    private $minification;

    /**
     * @var RepositoryMap
     */
    private $repositoryMap;

    /**
     * @param \Magento\Framework\RequireJs\Config\File\Collector\Aggregated $fileSource
     * @param \Magento\Framework\View\DesignInterface $design
     * @param ReadFactory $readFactory
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Code\Minifier\AdapterInterface $minifyAdapter
     * @param Minification $minification
     * @param RepositoryMap $repositoryMap
     */
    public function __construct(
        \Magento\Framework\RequireJs\Config\File\Collector\Aggregated $fileSource,
        \Magento\Framework\View\DesignInterface $design,
        ReadFactory $readFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Code\Minifier\AdapterInterface $minifyAdapter,
        Minification $minification,
        RepositoryMap $repositoryMap
    ) {
        $this->fileSource = $fileSource;
        $this->design = $design;
        $this->readFactory = $readFactory;
        $this->staticContext = $assetRepo->getStaticViewFileContext();
        $this->minifyAdapter = $minifyAdapter;
        $this->minification = $minification;
        $this->repositoryMap = $repositoryMap;
    }

    /**
     * Get aggregated distributed configuration
     *
     * @return string
     */
    public function getConfig()
    {
        $distributedConfig = '';
        $customConfigFiles = $this->fileSource->getFiles($this->design->getDesignTheme(), self::CONFIG_FILE_NAME);
        foreach ($customConfigFiles as $file) {
            /** @var $fileReader \Magento\Framework\Filesystem\File\Read */
            $fileReader = $this->readFactory->create($file->getFilename(), DriverPool::FILE);
            $config = $fileReader->readAll($file->getName());
            $distributedConfig .= str_replace(
                ['%config%', '%context%'],
                [$config, $file->getModule()],
                self::PARTIAL_CONFIG_TEMPLATE
            );
        }

        $fullConfig = str_replace(
            ['%function%', '%usages%'],
            [$distributedConfig],
            self::FULL_CONFIG_TEMPLATE
        );

        if ($this->minification->isEnabled('js')) {
            $fullConfig = $this->minifyAdapter->minify($fullConfig);
        }

        return $fullConfig;
    }

    /**
     * Get path to config file relative to directory, where all config files with different context are located
     *
     * @return string
     */
    public function getConfigFileRelativePath()
    {
        return $this->staticContext->getConfigPath() . '/' . $this->getConfigFileName();
    }

    /**
     * Get path to config file relative to directory, where all config files with different context are located
     *
     * @return string
     */
    public function getMixinsFileRelativePath()
    {
        $map = $this->getRepositoryFilesMap(Config::MIXINS_FILE_NAME, [
            'area' => $this->staticContext->getAreaCode(),
            'theme' => $this->staticContext->getThemePath(),
            'locale' => $this->staticContext->getLocale(),
        ]);
        if ($map) {
            $relativePath = implode('/', $map) . '/' . Config::MIXINS_FILE_NAME;
        } else {
            $relativePath = $this->staticContext->getPath() . '/' . self::MIXINS_FILE_NAME;
        }
        return $relativePath;
    }

    /**
     * Get path to config file relative to directory, where all config files with different context are located
     *
     * @return string
     */
    public function getRequireJsFileRelativePath()
    {
        return $this->staticContext->getConfigPath() . '/' . self::REQUIRE_JS_FILE_NAME;
    }

    /**
     * Get base RequireJs configuration necessary for working with Magento application
     *
     * @return string
     */
    public function getBaseConfig()
    {
        $config = [
            'baseUrl' => $this->staticContext->getBaseUrl() . $this->staticContext->getPath(),
        ];
        $config = json_encode($config, JSON_UNESCAPED_SLASHES);
        $result = "require.config($config);";
        return $result;
    }

    /**
     * Get path to '.min' files resolver relative to config files directory
     *
     * @return string
     */
    public function getMinResolverRelativePath()
    {
        return
            $this->staticContext->getConfigPath() .
            '/' .
            $this->minification->addMinifiedSign(self::MIN_RESOLVER_FILENAME);
    }

    /**
     * Get path to URL map resover file
     *
     * @return string
     */
    public function getUrlResolverFileRelativePath()
    {
        $map = $this->getRepositoryFilesMap(Config::URL_RESOLVER_FILE_NAME, [
            'area' => $this->staticContext->getAreaCode(),
            'theme' => $this->staticContext->getThemePath(),
            'locale' => $this->staticContext->getLocale(),
        ]);
        if ($map) {
            $relativePath = implode('/', $map) . '/' . Config::URL_RESOLVER_FILE_NAME;
        } else {
            $relativePath = $this->staticContext->getPath() . '/' . self::URL_RESOLVER_FILE_NAME;
        }
        return $relativePath;
    }

    /**
     * Get path to map file
     *
     * @return string
     */
    public function getMapFileRelativePath()
    {
        return $this->minification->addMinifiedSign($this->staticContext->getPath() . '/' . self::MAP_FILE_NAME);
    }

    /**
     * @return string
     */
    protected function getConfigFileName()
    {
        return $this->minification->addMinifiedSign(self::CONFIG_FILE_NAME);
    }

    /**
     * @return string
     */
    public function getMinResolverCode()
    {
        $excludes = [];
        foreach ($this->minification->getExcludes('js') as $expression) {
            $excludes[] = '!url.match(/' . str_replace('/', '\/', $expression) . '/)';
        }
        $excludesCode = empty($excludes) ? 'true' : implode('&&', $excludes);

        $result = <<<code
    var ctx = require.s.contexts._,
        origNameToUrl = ctx.nameToUrl;

    ctx.nameToUrl = function() {
        var url = origNameToUrl.apply(ctx, arguments);
        if ({$excludesCode}) {
            url = url.replace(/(\.min)?\.js$/, '.min.js');
        }
        return url;
    };

code;

        if ($this->minification->isEnabled('js')) {
            $result = $this->minifyAdapter->minify($result);
        }
        return $result;
    }

    /**
     * @param string $fileId
     * @param array $params
     * @return array
     */
    private function getRepositoryFilesMap($fileId, array $params)
    {
        return $this->repositoryMap->getMap($fileId, $params);
    }
}
