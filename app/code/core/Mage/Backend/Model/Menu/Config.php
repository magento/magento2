<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Backend_Model_Menu_Config
{
    const CACHE_ID = 'backend_menu_config';
    const CACHE_MENU_OBJECT = 'backend_menu_object';

    /**
     * @var Mage_Core_Model_Cache
     */
    protected $_cache;

    /**
     * @var Magento_ObjectManager
     */
    protected $_factory;

    /**
     * @var Mage_Core_Model_Config
     */
    protected $_appConfig;

    /**
     * @var Mage_Core_Model_Event_Manager
     */
    protected $_eventManager;

    /**
     * @var Mage_Backend_Model_Menu_Factory
     */
    protected $_menuFactory;
    /**
     * Menu model
     *
     * @var Mage_Backend_Model_Menu
     */
    protected $_menu;

    /**
     * @var Mage_Core_Model_Logger
     */
    protected $_logger;

    /**
     * @param Mage_Core_Model_Cache $cache
     * @param Magento_ObjectManager $factory
     * @param Mage_Core_Model_Config $config
     * @param Mage_Core_Model_Event_Manager $eventManager
     * @param Mage_Core_Model_Logger $logger
     * @param Mage_Backend_Model_Menu_Factory $menuFactory
     */
    public function __construct(
        Mage_Core_Model_Cache $cache,
        Magento_ObjectManager $factory,
        Mage_Core_Model_Config $config,
        Mage_Core_Model_Event_Manager $eventManager,
        Mage_Core_Model_Logger $logger,
        Mage_Backend_Model_Menu_Factory $menuFactory
    ) {
        $this->_cache = $cache;
        $this->_factory = $factory;
        $this->_appConfig = $config;
        $this->_eventManager = $eventManager;
        $this->_logger = $logger;
        $this->_menuFactory = $menuFactory;
    }

    /**
     * Build menu model from config
     *
     * @return Mage_Backend_Model_Menu
     * @throws InvalidArgumentException|BadMethodCallException|OutOfRangeException|Exception
     */
    public function getMenu()
    {
        $store = $this->_factory->get('Mage_Core_Model_App')->getStore();
        $this->_logger->addStoreLog(Mage_Backend_Model_Menu::LOGGER_KEY, $store);
        try {
            $this->_initMenu();
            return $this->_menu;
        } catch (InvalidArgumentException $e) {
            $this->_logger->logException($e);
            throw $e;
        } catch (BadMethodCallException $e) {
            $this->_logger->logException($e);
            throw $e;
        } catch (OutOfRangeException $e) {
            $this->_logger->logException($e);
            throw $e;
        } catch (Exception $e) {
            throw $e;
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
            $this->_menu = $this->_menuFactory->getMenuInstance();

            if ($this->_cache->canUse('config')) {
                $cache = $this->_cache->load(self::CACHE_MENU_OBJECT);
                if ($cache) {
                    $this->_menu->unserialize($cache);
                    return;
                }
            }

            /* @var $director Mage_Backend_Model_Menu_Builder */
            $menuBuilder = $this->_factory->create('Mage_Backend_Model_Menu_Builder', array(
                'menu' => $this->_menu,
                'menuItemFactory' => $this->_factory->get('Mage_Backend_Model_Menu_Item_Factory'),
            ));

            /* @var $director Mage_Backend_Model_Menu_Director_Dom */
            $director = $this->_factory->create(
                'Mage_Backend_Model_Menu_Director_Dom',
                array(
                    'menuConfig' => $this->_getDom(),
                    'factory' => $this->_factory,
                    'menuLogger' => $this->_logger
                )
            );
            $director->buildMenu($menuBuilder);
            $this->_menu = $menuBuilder->getResult();
            $this->_eventManager->dispatch('backend_menu_load_after', array('menu' => $this->_menu));

            if ($this->_cache->canUse('config')) {
                $this->_cache->save(
                    $this->_menu->serialize(),
                    self::CACHE_MENU_OBJECT,
                    array(Mage_Core_Model_Config::CACHE_TAG)
                );
            }
        }
    }

    /**
     * @return DOMDocument
     */
    protected function _getDom()
    {
        $mergedConfigXml = $this->_loadCache();
        if ($mergedConfigXml) {
            $mergedConfig = new DOMDocument();
            $mergedConfig->loadXML($mergedConfigXml);
        } else {
            $fileList = $this->getMenuConfigurationFiles();
            $mergedConfig = $this->_factory
                ->create('Mage_Backend_Model_Menu_Config_Menu', array('configFiles' => $fileList))
                ->getMergedConfig();
            $this->_saveCache($mergedConfig->saveXML());
        }
        return $mergedConfig;
    }

    protected function _loadCache()
    {
        if ($this->_cache->canUse('config')) {
            return $this->_cache->load(self::CACHE_ID);
        }
        return false;
    }

    protected function _saveCache($xml)
    {
        if ($this->_cache->canUse('config')) {
            $this->_cache->save($xml, self::CACHE_ID, array(Mage_Core_Model_Config::CACHE_TAG));
        }
        return $this;
    }

    /**
     * Return array menu configuration files
     *
     * @return array
     */
    public function getMenuConfigurationFiles()
    {
        $files = $this->_appConfig
            ->getModuleConfigurationFiles('adminhtml' . DIRECTORY_SEPARATOR . 'menu.xml');
        return (array) $files;
    }
}
