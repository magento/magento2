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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class for choosing the strategy for file resolution
 */
namespace Magento\Core\Model\Design\FileResolution;

class StrategyPool
{
    /**
     * Sub-directory where to store maps of view files fallback (if used)
     */
    const FALLBACK_MAP_DIR = 'maps/fallback';

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var string
     */
    protected $_appState;

    /**
     * @var \Magento\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\App\Dir
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
            'file' => 'Magento\Core\Model\Design\FileResolution\Strategy\Fallback\CachingProxy',
            'locale' => 'Magento\Core\Model\Design\FileResolution\Strategy\Fallback',
            'view' => 'Magento\Core\Model\Design\FileResolution\Strategy\Fallback',
        ),
        'caching_map' => array(
            'file' => 'Magento\Core\Model\Design\FileResolution\Strategy\Fallback\CachingProxy',
            'locale' => 'Magento\Core\Model\Design\FileResolution\Strategy\Fallback\CachingProxy',
            'view' => 'Magento\Core\Model\Design\FileResolution\Strategy\Fallback\CachingProxy',
        ),
        'full_check' => array(
            'file' => 'Magento\Core\Model\Design\FileResolution\Strategy\Fallback',
            'locale' => 'Magento\Core\Model\Design\FileResolution\Strategy\Fallback',
            'view' => 'Magento\Core\Model\Design\FileResolution\Strategy\Fallback',
        ),
    );

    /**
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\App\State $appState
     * @param \Magento\App\Dir $dirs
     * @param \Magento\Filesystem $filesystem
     */
    public function __construct(
        \Magento\ObjectManager $objectManager,
        \Magento\App\State $appState,
        \Magento\App\Dir $dirs,
        \Magento\Filesystem $filesystem
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
     * @return \Magento\Core\Model\Design\FileResolution\Strategy\FileInterface
     */
    public function getFileStrategy($skipProxy = false)
    {
        return $this->_getStrategy('file', $skipProxy);
    }

    /**
     * * Get strategy to resolve locale files (e.g. locale settings)
     *
     * @param bool $skipProxy
     * @return \Magento\Core\Model\Design\FileResolution\Strategy\LocaleInterface
     */
    public function getLocaleStrategy($skipProxy = false)
    {
        return $this->_getStrategy('locale', $skipProxy);
    }

    /**
     * Get strategy to resolve static view files (e.g. javascripts)
     *
     * @param bool $skipProxy
     * @return \Magento\Core\Model\Design\FileResolution\Strategy\ViewInterface
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
     * @throws \Magento\Core\Exception
     */
    protected function _getStrategyClass($fileType, $skipProxy = false)
    {
        $mode = $this->_appState->getMode();
        if ($mode == \Magento\App\State::MODE_PRODUCTION) {
            $strategyClasses = $this->_strategies['production_mode'];
        } else if (($mode == \Magento\App\State::MODE_DEVELOPER) || $skipProxy) {
            $strategyClasses = $this->_strategies['full_check'];
        } else if ($mode == \Magento\App\State::MODE_DEFAULT) {
            $strategyClasses = $this->_strategies['caching_map'];
        } else {
            throw new \Magento\Core\Exception("Unknown mode to choose strategy: {$mode}");
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
            case 'Magento\Core\Model\Design\FileResolution\Strategy\Fallback\CachingProxy':
                $mapDir = $this->_dirs->getDir(\Magento\App\Dir::VAR_DIR) . DIRECTORY_SEPARATOR
                    . self::FALLBACK_MAP_DIR;
                $arguments = array(
                    'mapDir' => str_replace('/', DIRECTORY_SEPARATOR, $mapDir),
                    'baseDir' => $this->_dirs->getDir(\Magento\App\Dir::ROOT),
                );
                break;
            default:
                $arguments = array();
                break;
        }
        return $this->_objectManager->create($className, $arguments);
    }
}
