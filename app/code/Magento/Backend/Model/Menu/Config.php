<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Menu;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
 */
class Config
{
    public const CACHE_ID = 'backend_menu_config';

    public const CACHE_MENU_OBJECT = 'backend_menu_object';

    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     */
    protected $_configCacheType;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Backend\Model\MenuFactory
     */
    protected $_menuFactory;

    /**
     * Menu model
     *
     * @var \Magento\Backend\Model\Menu
     */
    protected $_menu;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Backend\Model\Menu\Config\Reader
     */
    protected $_configReader;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Backend\Model\Menu\AbstractDirector
     */
    protected $_director;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @var Builder
     */
    private $_menuBuilder;

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
     * @throws LocalizedException
     */
    public function getMenu()
    {
        try {
            $this->_initMenu();
            return $this->_menu;
        } catch (\InvalidArgumentException $e) {
            $this->_logger->critical($e);
            throw new LocalizedException(
                new Phrase(
                    'An error occurred while building the admin menu. '
                    . 'Please check your menu.xml files for missing required attributes '
                    . '(e.g. \'parent\' or \'action\'). Original error: %1',
                    [$e->getMessage()]
                ),
                $e
            );
        } catch (\BadMethodCallException $e) {
            $this->_logger->critical($e);
            throw new LocalizedException(
                new Phrase(
                    'An error occurred while building the admin menu. '
                    . 'Please check your menu.xml files for missing required parameters. '
                    . 'Original error: %1',
                    [$e->getMessage()]
                ),
                $e
            );
        } catch (\OutOfRangeException $e) {
            $this->_logger->critical($e);
            throw new LocalizedException(
                new Phrase(
                    'An error occurred while building the admin menu. '
                    . 'A menu item references a non-existent parent item. '
                    . 'Please check your menu.xml files. Original error: %1',
                    [$e->getMessage()]
                ),
                $e
            );
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            throw new LocalizedException(
                new Phrase(
                    'An unexpected error occurred while building the admin menu. '
                    . 'Original error: %1',
                    [$e->getMessage()]
                ),
                $e
            );
        }
    }

    /**
     * Initialize menu object
     *
     * @return void
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
