<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ModuleManager;

use Traversable;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;

/**
 * Module manager
 */
class ModuleManager implements ModuleManagerInterface
{
    /**#@+
     * Reference to Zend\Mvc\MvcEvent::EVENT_BOOTSTRAP
     */
    const EVENT_BOOTSTRAP = 'bootstrap';
    /**#@-*/

    /**
     * @var array An array of Module classes of loaded modules
     */
    protected $loadedModules = array();

    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * @var ModuleEvent
     */
    protected $event;

    /**
     * @var bool
     */
    protected $loadFinished;

    /**
     * modules
     *
     * @var array|Traversable
     */
    protected $modules = array();

    /**
     * True if modules have already been loaded
     *
     * @var bool
     */
    protected $modulesAreLoaded = false;

    /**
     * Constructor
     *
     * @param  array|Traversable $modules
     * @param  EventManagerInterface $eventManager
     */
    public function __construct($modules, EventManagerInterface $eventManager = null)
    {
        $this->setModules($modules);
        if ($eventManager instanceof EventManagerInterface) {
            $this->setEventManager($eventManager);
        }
    }

    /**
     * Handle the loadModules event
     *
     * @return void
     */
    public function onLoadModules()
    {
        if (true === $this->modulesAreLoaded) {
            return $this;
        }

        foreach ($this->getModules() as $moduleName => $module) {
            if (is_object($module)) {
                if (!is_string($moduleName)) {
                    throw new Exception\RuntimeException(sprintf(
                        'Module (%s) must have a key identifier.',
                        get_class($module)
                    ));
                }
                $module = array($moduleName => $module);
            }
            $this->loadModule($module);
        }

        $this->modulesAreLoaded = true;
    }

    /**
     * Load the provided modules.
     *
     * @triggers loadModules
     * @triggers loadModules.post
     * @return   ModuleManager
     */
    public function loadModules()
    {
        if (true === $this->modulesAreLoaded) {
            return $this;
        }

        $this->getEventManager()->trigger(ModuleEvent::EVENT_LOAD_MODULES, $this, $this->getEvent());

        /**
         * Having a dedicated .post event abstracts the complexity of priorities from the user.
         * Users can attach to the .post event and be sure that important
         * things like config merging are complete without having to worry if
         * they set a low enough priority.
         */
        $this->getEventManager()->trigger(ModuleEvent::EVENT_LOAD_MODULES_POST, $this, $this->getEvent());

        return $this;
    }

    /**
     * Load a specific module by name.
     *
     * @param  string|array               $module
     * @throws Exception\RuntimeException
     * @triggers loadModule.resolve
     * @triggers loadModule
     * @return mixed Module's Module class
     */
    public function loadModule($module)
    {
        $moduleName = $module;
        if (is_array($module)) {
            $moduleName = key($module);
            $module = current($module);
        }

        if (isset($this->loadedModules[$moduleName])) {
            return $this->loadedModules[$moduleName];
        }

        /*
         * Keep track of nested module loading using the $loadFinished
         * property.
         *
         * Increment the value for each loadModule() call and then decrement
         * once the loading process is complete.
         *
         * To load a module, we clone the event if we are inside a nested
         * loadModule() call, and use the original event otherwise.
         */
        if (!isset($this->loadFinished)) {
            $this->loadFinished = 0;
        }

        $event = ($this->loadFinished > 0) ? clone $this->getEvent() : $this->getEvent();
        $event->setModuleName($moduleName);

        $this->loadFinished++;

        if (!is_object($module)) {
            $module = $this->loadModuleByName($event);
        }
        $event->setModule($module);

        $this->loadedModules[$moduleName] = $module;
        $this->getEventManager()->trigger(ModuleEvent::EVENT_LOAD_MODULE, $this, $event);

        $this->loadFinished--;

        return $module;
    }

    /**
     * Load a module with the name
     * @param  \Zend\EventManager\EventInterface $event
     * @return mixed                            module instance
     * @throws Exception\RuntimeException
     */
    protected function loadModuleByName($event)
    {
        $result = $this->getEventManager()->trigger(ModuleEvent::EVENT_LOAD_MODULE_RESOLVE, $this, $event, function ($r) {
            return (is_object($r));
        });

        $module = $result->last();
        if (!is_object($module)) {
            throw new Exception\RuntimeException(sprintf(
                'Module (%s) could not be initialized.',
                $event->getModuleName()
            ));
        }

        return $module;
    }

    /**
     * Get an array of the loaded modules.
     *
     * @param  bool  $loadModules If true, load modules if they're not already
     * @return array An array of Module objects, keyed by module name
     */
    public function getLoadedModules($loadModules = false)
    {
        if (true === $loadModules) {
            $this->loadModules();
        }

        return $this->loadedModules;
    }

    /**
     * Get an instance of a module class by the module name
     *
     * @param  string $moduleName
     * @return mixed
     */
    public function getModule($moduleName)
    {
        if (!isset($this->loadedModules[$moduleName])) {
            return;
        }
        return $this->loadedModules[$moduleName];
    }

    /**
     * Get the array of module names that this manager should load.
     *
     * @return array
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Set an array or Traversable of module names that this module manager should load.
     *
     * @param  mixed $modules array or Traversable of module names
     * @throws Exception\InvalidArgumentException
     * @return ModuleManager
     */
    public function setModules($modules)
    {
        if (is_array($modules) || $modules instanceof Traversable) {
            $this->modules = $modules;
        } else {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Parameter to %s\'s %s method must be an array or implement the Traversable interface',
                    __CLASS__,
                    __METHOD__
                )
            );
        }
        return $this;
    }

    /**
     * Get the module event
     *
     * @return ModuleEvent
     */
    public function getEvent()
    {
        if (!$this->event instanceof ModuleEvent) {
            $this->setEvent(new ModuleEvent);
        }
        return $this->event;
    }

    /**
     * Set the module event
     *
     * @param  ModuleEvent $event
     * @return ModuleManager
     */
    public function setEvent(ModuleEvent $event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * Set the event manager instance used by this module manager.
     *
     * @param  EventManagerInterface $events
     * @return ModuleManager
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            __CLASS__,
            get_class($this),
            'module_manager',
        ));
        $this->events = $events;
        $this->attachDefaultListeners();
        return $this;
    }

    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (!$this->events instanceof EventManagerInterface) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }

    /**
     * Register the default event listeners
     *
     * @return ModuleManager
     */
    protected function attachDefaultListeners()
    {
        $events = $this->getEventManager();
        $events->attach(ModuleEvent::EVENT_LOAD_MODULES, array($this, 'onLoadModules'));
    }
}
