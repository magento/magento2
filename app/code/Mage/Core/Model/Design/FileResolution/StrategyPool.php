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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class for choosing the strategy for file resolution
 */
class Mage_Core_Model_Design_FileResolution_StrategyPool
{
    /**
     * Path to config node that allows automatically updating map files in runtime
     */
    const XML_PATH_ALLOW_MAP_UPDATE = 'global/dev/design_fallback/allow_map_update';

    /**
     * Sub-directory where to store maps of view files fallback (if used)
     */
    const FALLBACK_MAP_DIR = 'maps/fallback';

    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var string
     */
    protected $_appState;

    /**
     * @var Magento_Filesystem
     */
    protected $_filesystem;

    /**
     * @var Mage_Core_Model_Dir
     */
    protected $_dirs;

    /**
     * Pool of strategy objects
     *
     * @var array
     */
    protected $_strategyPool = array();

    /**
     * Settings for strategies that are used to resolve file paths
     *
     * @var array
     */
    protected $_strategies = array(
        'production_mode' => array(
            'file' => 'Mage_Core_Model_Design_FileResolution_Strategy_Fallback_CachingProxy',
            'locale' => 'Mage_Core_Model_Design_FileResolution_Strategy_Fallback',
            'view' => 'Mage_Core_Model_Design_FileResolution_Strategy_Fallback',
        ),
        'caching_map' => array(
            'file' => 'Mage_Core_Model_Design_FileResolution_Strategy_Fallback_CachingProxy',
            'locale' => 'Mage_Core_Model_Design_FileResolution_Strategy_Fallback_CachingProxy',
            'view' => 'Mage_Core_Model_Design_FileResolution_Strategy_Fallback_CachingProxy',
        ),
        'full_check' => array(
            'file' => 'Mage_Core_Model_Design_FileResolution_Strategy_Fallback',
            'locale' => 'Mage_Core_Model_Design_FileResolution_Strategy_Fallback',
            'view' => 'Mage_Core_Model_Design_FileResolution_Strategy_Fallback',
        ),
    );

    /**
     * @param Magento_ObjectManager $objectManager
     * @param Mage_Core_Model_App_State $appState
     * @param Mage_Core_Model_Dir $dirs
     * @param Magento_Filesystem $filesystem
     */
    public function __construct(
        Magento_ObjectManager $objectManager,
        Mage_Core_Model_App_State $appState,
        Mage_Core_Model_Dir $dirs,
        Magento_Filesystem $filesystem
    ) {
        $this->_objectManager = $objectManager;
        $this->_appState = $appState;
        $this->_filesystem = $filesystem;
        $this->_dirs = $dirs;
    }

    /**
     * Get strategy to resolve dynamic files (e.g. templates)
     *
     * @param bool $skipProxy
     * @return Mage_Core_Model_Design_FileResolution_Strategy_FileInterface
     */
    public function getFileStrategy($skipProxy = false)
    {
        return $this->_getStrategy('file', $skipProxy);
    }

    /**
     * * Get strategy to resolve locale files (e.g. locale settings)
     *
     * @param bool $skipProxy
     * @return Mage_Core_Model_Design_FileResolution_Strategy_LocaleInterface
     */
    public function getLocaleStrategy($skipProxy = false)
    {
        return $this->_getStrategy('locale', $skipProxy);
    }

    /**
     * Get strategy to resolve static view files (e.g. javascripts)
     *
     * @param bool $skipProxy
     * @return Mage_Core_Model_Design_FileResolution_Strategy_ViewInterface
     */
    public function getViewStrategy($skipProxy = false)
    {
        return $this->_getStrategy('view', $skipProxy);
    }

    /**
     * Determine the strategy to be used. Create or get it from the pool.
     *
     * @param string $fileType
     * @param bool $skipProxy
     * @return mixed
     */
    protected function _getStrategy($fileType, $skipProxy = false)
    {
        $strategyClass = $this->_getStrategyClass($fileType, $skipProxy);
        if (!isset($this->_strategyPool[$strategyClass])) {
            $this->_strategyPool[$strategyClass] = $this->_createStrategy($strategyClass);
        }
        return $this->_strategyPool[$strategyClass];
    }

    /**
     * Find the class of strategy, that must be used to resolve files of $fileType
     *
     * @param string $fileType
     * @param bool $skipProxy
     * @return string
     * @throws Mage_Core_Exception
     */
    protected function _getStrategyClass($fileType, $skipProxy = false)
    {
        $mode = $this->_appState->getMode();
        if ($mode == Mage_Core_Model_App_State::MODE_PRODUCTION) {
            $strategyClasses = $this->_strategies['production_mode'];
        } else if (($mode == Mage_Core_Model_App_State::MODE_DEVELOPER) || $skipProxy) {
            $strategyClasses = $this->_strategies['full_check'];
        } else if ($mode == Mage_Core_Model_App_State::MODE_DEFAULT) {
            $strategyClasses = $this->_strategies['caching_map'];
        } else {
            throw new Mage_Core_Exception("Unknown mode to choose strategy: {$mode}");
        }
        return $strategyClasses[$fileType];
    }

    /**
     * Create strategy by its class name
     *
     * @param string $className
     * @return mixed
     */
    protected function _createStrategy($className)
    {
        switch ($className) {
            case 'Mage_Core_Model_Design_FileResolution_Strategy_Fallback_CachingProxy':
                $mapDir = $this->_dirs->getDir(Mage_Core_Model_Dir::VAR_DIR) . DIRECTORY_SEPARATOR
                    . self::FALLBACK_MAP_DIR;
                $arguments = array(
                    'mapDir' => str_replace('/', DIRECTORY_SEPARATOR, $mapDir),
                    'baseDir' => $this->_dirs->getDir(Mage_Core_Model_Dir::ROOT),
                    'canSaveMap' => (bool)(string)$this->_objectManager->get('Mage_Core_Model_Config')
                        ->getNode(self::XML_PATH_ALLOW_MAP_UPDATE),
                );
                break;
            default:
                $arguments = array();
                break;
        }
        return $this->_objectManager->create($className, $arguments);
    }
}
