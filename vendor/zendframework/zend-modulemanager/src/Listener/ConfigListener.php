<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ModuleManager\Listener;

use Traversable;
use Zend\Config\Config;
use Zend\Config\Factory as ConfigFactory;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\ModuleEvent;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\Glob;

/**
 * Config listener
 */
class ConfigListener extends AbstractListener implements
    ConfigMergerInterface,
    ListenerAggregateInterface
{
    const STATIC_PATH = 'static_path';
    const GLOB_PATH   = 'glob_path';

    /**
     * @var array
     */
    protected $callbacks = array();

    /**
     * @var array
     */
    protected $configs = array();

    /**
     * @var array
     */
    protected $mergedConfig = array();

    /**
     * @var Config
     */
    protected $mergedConfigObject;

    /**
     * @var bool
     */
    protected $skipConfig = false;

    /**
     * @var array
     */
    protected $paths = array();

    /**
     * __construct
     *
     * @param  ListenerOptions $options
     */
    public function __construct(ListenerOptions $options = null)
    {
        parent::__construct($options);
        if ($this->hasCachedConfig()) {
            $this->skipConfig = true;
            $this->setMergedConfig($this->getCachedConfig());
        } else {
            $this->addConfigGlobPaths($this->getOptions()->getConfigGlobPaths());
            $this->addConfigStaticPaths($this->getOptions()->getConfigStaticPaths());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->callbacks[] = $events->attach(ModuleEvent::EVENT_LOAD_MODULES, array($this, 'onloadModulesPre'), 1000);

        if ($this->skipConfig) {
            // We already have the config from cache, no need to collect or merge.
            return $this;
        }

        $this->callbacks[] = $events->attach(ModuleEvent::EVENT_LOAD_MODULE, array($this, 'onLoadModule'));
        $this->callbacks[] = $events->attach(ModuleEvent::EVENT_LOAD_MODULES, array($this, 'onLoadModules'), -1000);
        $this->callbacks[] = $events->attach(ModuleEvent::EVENT_MERGE_CONFIG, array($this, 'onMergeConfig'), 1000);
        return $this;
    }

    /**
     * Pass self to the ModuleEvent object early so everyone has access.
     *
     * @param  ModuleEvent $e
     * @return ConfigListener
     */
    public function onloadModulesPre(ModuleEvent $e)
    {
        $e->setConfigListener($this);

        return $this;
    }

    /**
     * Merge the config for each module
     *
     * @param  ModuleEvent $e
     * @return ConfigListener
     */
    public function onLoadModule(ModuleEvent $e)
    {
        $module = $e->getModule();

        if (!$module instanceof ConfigProviderInterface
            && !is_callable(array($module, 'getConfig'))
        ) {
            return $this;
        }

        $config = $module->getConfig();
        $this->addConfig($e->getModuleName(), $config);

        return $this;
    }

    /**
     * Merge all config files matched by the given glob()s
     *
     * This is only attached if config is not cached.
     *
     * @param  ModuleEvent $e
     * @return ConfigListener
     */
    public function onMergeConfig(ModuleEvent $e)
    {
        // Load the config files
        foreach ($this->paths as $path) {
            $this->addConfigByPath($path['path'], $path['type']);
        }

        // Merge all of the collected configs
        $this->mergedConfig = $this->getOptions()->getExtraConfig() ?: array();
        foreach ($this->configs as $config) {
            $this->mergedConfig = ArrayUtils::merge($this->mergedConfig, $config);
        }

        return $this;
    }

    /**
     * Optionally cache merged config
     *
     * This is only attached if config is not cached.
     *
     * @param  ModuleEvent $e
     * @return ConfigListener
     */
    public function onLoadModules(ModuleEvent $e)
    {
        // Trigger MERGE_CONFIG event. This is a hook to allow the merged application config to be
        // modified before it is cached (In particular, allows the removal of config keys)
        $e->getTarget()->getEventManager()->trigger(ModuleEvent::EVENT_MERGE_CONFIG, $e->getTarget(), $e);

        // If enabled, update the config cache
        if (
            $this->getOptions()->getConfigCacheEnabled()
            && false === $this->skipConfig
        ) {
            $configFile = $this->getOptions()->getConfigCacheFile();
            $this->writeArrayToFile($configFile, $this->getMergedConfig(false));
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->callbacks as $index => $callback) {
            if ($events->detach($callback)) {
                unset($this->callbacks[$index]);
            }
        }
    }

    /**
     * getMergedConfig
     *
     * @param  bool $returnConfigAsObject
     * @return mixed
     */
    public function getMergedConfig($returnConfigAsObject = true)
    {
        if ($returnConfigAsObject === true) {
            if ($this->mergedConfigObject === null) {
                $this->mergedConfigObject = new Config($this->mergedConfig);
            }
            return $this->mergedConfigObject;
        }

        return $this->mergedConfig;
    }

    /**
     * setMergedConfig
     *
     * @param  array $config
     * @return ConfigListener
     */
    public function setMergedConfig(array $config)
    {
        $this->mergedConfig = $config;
        $this->mergedConfigObject = null;
        return $this;
    }

    /**
     * Add an array of glob paths of config files to merge after loading modules
     *
     * @param  array|Traversable $globPaths
     * @return ConfigListener
     */
    public function addConfigGlobPaths($globPaths)
    {
        $this->addConfigPaths($globPaths, self::GLOB_PATH);
        return $this;
    }

    /**
     * Add a glob path of config files to merge after loading modules
     *
     * @param  string $globPath
     * @return ConfigListener
     */
    public function addConfigGlobPath($globPath)
    {
        $this->addConfigPath($globPath, self::GLOB_PATH);
        return $this;
    }

    /**
     * Add an array of static paths of config files to merge after loading modules
     *
     * @param  array|Traversable $staticPaths
     * @return ConfigListener
     */
    public function addConfigStaticPaths($staticPaths)
    {
        $this->addConfigPaths($staticPaths, self::STATIC_PATH);
        return $this;
    }

    /**
     * Add a static path of config files to merge after loading modules
     *
     * @param  string $staticPath
     * @return ConfigListener
     */
    public function addConfigStaticPath($staticPath)
    {
        $this->addConfigPath($staticPath, self::STATIC_PATH);
        return $this;
    }

    /**
     * Add an array of paths of config files to merge after loading modules
     *
     * @param  Traversable|array $paths
     * @param string $type
     * @throws Exception\InvalidArgumentException
     * @return ConfigListener
     */
    protected function addConfigPaths($paths, $type)
    {
        if ($paths instanceof Traversable) {
            $paths = ArrayUtils::iteratorToArray($paths);
        }

        if (!is_array($paths)) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Argument passed to %::%s() must be an array, '
                    . 'implement the Traversable interface, or be an '
                    . 'instance of Zend\Config\Config. %s given.',
                    __CLASS__,
                    __METHOD__,
                    gettype($paths)
                )
            );
        }

        foreach ($paths as $path) {
            $this->addConfigPath($path, $type);
        }
    }

    /**
     * Add a path of config files to load and merge after loading modules
     *
     * @param  string $path
     * @param  string $type
     * @throws Exception\InvalidArgumentException
     * @return ConfigListener
     */
    protected function addConfigPath($path, $type)
    {
        if (!is_string($path)) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Parameter to %s::%s() must be a string; %s given.',
                    __CLASS__,
                    __METHOD__,
                    gettype($path)
                )
            );
        }
        $this->paths[] = array('type' => $type, 'path' => $path);
        return $this;
    }

    /**
     * @param string $key
     * @param array|Traversable $config
     * @throws Exception\InvalidArgumentException
     * @return ConfigListener
     */
    protected function addConfig($key, $config)
    {
        if ($config instanceof Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        }

        if (!is_array($config)) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Config being merged must be an array, '
                    . 'implement the Traversable interface, or be an '
                    . 'instance of Zend\Config\Config. %s given.',
                    gettype($config)
                )
            );
        }

        $this->configs[$key] = $config;

        return $this;
    }

    /**
     * Given a path (glob or static), fetch the config and add it to the array
     * of configs to merge.
     *
     * @param string $path
     * @param string $type
     * @return ConfigListener
     */
    protected function addConfigByPath($path, $type)
    {
        switch ($type) {
            case self::STATIC_PATH:
                $this->addConfig($path, ConfigFactory::fromFile($path));
                break;

            case self::GLOB_PATH:
                // We want to keep track of where each value came from so we don't
                // use ConfigFactory::fromFiles() since it does merging internally.
                foreach (Glob::glob($path, Glob::GLOB_BRACE) as $file) {
                    $this->addConfig($file, ConfigFactory::fromFile($file));
                }
                break;
        }

        return $this;
    }

    /**
     * @return bool
     */
    protected function hasCachedConfig()
    {
        if (($this->getOptions()->getConfigCacheEnabled())
            && (file_exists($this->getOptions()->getConfigCacheFile()))
        ) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    protected function getCachedConfig()
    {
        return include $this->getOptions()->getConfigCacheFile();
    }
}
