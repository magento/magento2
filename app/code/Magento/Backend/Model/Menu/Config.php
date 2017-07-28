<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Menu;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 2.0.0
 */
class Config
{
    const CACHE_ID = 'backend_menu_config';

    const CACHE_MENU_OBJECT = 'backend_menu_object';

    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     * @since 2.0.0
     */
    protected $_configCacheType;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    protected $_eventManager;

    /**
     * @var \Magento\Backend\Model\MenuFactory
     * @since 2.0.0
     */
    protected $_menuFactory;

    /**
     * Menu model
     *
     * @var \Magento\Backend\Model\Menu
     * @since 2.0.0
     */
    protected $_menu;

    /**
     * @var \Psr\Log\LoggerInterface
     * @since 2.0.0
     */
    protected $_logger;

    /**
     * @var \Magento\Backend\Model\Menu\Config\Reader
     * @since 2.0.0
     */
    protected $_configReader;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Backend\Model\Menu\AbstractDirector
     * @since 2.0.0
     */
    protected $_director;

    /**
     * @var \Magento\Framework\App\State
     * @since 2.0.0
     */
    protected $_appState;

    /**
     * @param \Magento\Backend\Model\Menu\Builder $menuBuilder
     * @param \Magento\Backend\Model\Menu\AbstractDirector $menuDirector
     * @param \Magento\Backend\Model\MenuFactory $menuFactory
     * @param \Magento\Backend\Model\Menu\Config\Reader $configReader
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\State $appState
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Model\Menu\Builder $menuBuilder,
        \Magento\Backend\Model\Menu\AbstractDirector $menuDirector,
        \Magento\Backend\Model\MenuFactory $menuFactory,
        \Magento\Backend\Model\Menu\Config\Reader $configReader,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\State $appState
    ) {
        $this->_menuBuilder = $menuBuilder;
        $this->_director = $menuDirector;
        $this->_configCacheType = $configCacheType;
        $this->_eventManager = $eventManager;
        $this->_logger = $logger;
        $this->_menuFactory = $menuFactory;
        $this->_configReader = $configReader;
        $this->_scopeConfig = $scopeConfig;
        $this->_appState = $appState;
    }

    /**
     * Build menu model from config
     *
     * @return \Magento\Backend\Model\Menu
     * @throws \Exception|\InvalidArgumentException
     * @throws \Exception
     * @throws \BadMethodCallException|\Exception
     * @throws \Exception|\OutOfRangeException
     * @since 2.0.0
     */
    public function getMenu()
    {
        try {
            $this->_initMenu();
            return $this->_menu;
        } catch (\InvalidArgumentException $e) {
            $this->_logger->critical($e);
            throw $e;
        } catch (\BadMethodCallException $e) {
            $this->_logger->critical($e);
            throw $e;
        } catch (\OutOfRangeException $e) {
            $this->_logger->critical($e);
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Initialize menu object
     *
     * @return void
     * @since 2.0.0
     */
    protected function _initMenu()
    {
        if (!$this->_menu) {
            $this->_menu = $this->_menuFactory->create();

            $cache = $this->_configCacheType->load(self::CACHE_MENU_OBJECT);
            if ($cache) {
                $this->_menu->unserialize($cache);
                return;
            }

            $this->_director->direct(
                $this->_configReader->read($this->_appState->getAreaCode()),
                $this->_menuBuilder,
                $this->_logger
            );
            $this->_menu = $this->_menuBuilder->getResult($this->_menu);

            $this->_configCacheType->save($this->_menu->serialize(), self::CACHE_MENU_OBJECT);
        }
    }
}
