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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\View;

/**
 * Design service model
 */
class Service
{
    /**
     * Scope separator
     */
    const SCOPE_SEPARATOR = '::';

    /**
     * Application state
     *
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * Design
     *
     * @var \Magento\View\DesignInterface
     */
    private $_design;

    /**
     * Theme factory
     *
     * @var \Magento\View\Design\Theme\FlyweightFactory
     */
    protected $themeFactory;

    /**
     * Pub directory
     *
     * @var string
     */
    protected $_pubDirectory;

    /**
     * Constructor
     *
     * @param \Magento\App\State $appState
     * @param \Magento\View\DesignInterface $design
     * @param \Magento\View\Design\Theme\FlyweightFactory $themeFactory
     * @param \Magento\App\Filesystem $filesystem
     */
    public function __construct(
        \Magento\App\State $appState,
        \Magento\View\DesignInterface $design,
        \Magento\View\Design\Theme\FlyweightFactory $themeFactory,
        \Magento\App\Filesystem $filesystem
    ) {
        $this->_appState = $appState;
        $this->_design = $design;
        $this->_pubDirectory = $filesystem->getPath(\Magento\App\Filesystem::STATIC_VIEW_DIR);
        $this->themeFactory = $themeFactory;
    }

    /**
     * Identify file scope if it defined in file name and override 'module' parameter in $params array
     *
     * It accepts $fileId e.g. \Magento\Core::prototype/magento.css and splits it to module part and path part.
     * Then sets module path to $params['module'] and returns path part.
     *
     * @param string $fileId
     * @param array &$params
     * @return string
     * @throws \Magento\Exception
     */
    public function extractScope($fileId, array &$params)
    {
        if (strpos(str_replace('\\', '/', $fileId), './') !== false) {
            throw new \Magento\Exception("File name '{$fileId}' is forbidden for security reasons.");
        }
        if (strpos($fileId, self::SCOPE_SEPARATOR) === false) {
            $file = $fileId;
        } else {
            $fileId = explode(self::SCOPE_SEPARATOR, $fileId);
            if (empty($fileId[0])) {
                throw new \Magento\Exception('Scope separator "::" cannot be used without scope identifier.');
            }
            $params['module'] = $fileId[0];
            $file = $fileId[1];
        }
        return $file;
    }

    /**
     * Verify whether we should work with files
     *
     * @return bool
     */
    public function isViewFileOperationAllowed()
    {
        return $this->getAppMode() != \Magento\App\State::MODE_PRODUCTION;
    }

    /**
     * Return whether developer mode is turned on
     *
     * @return string
     */
    public function getAppMode()
    {
        return $this->_appState->getMode();
    }

    /**
     * Return directory for theme files publication
     *
     * @return string
     */
    public function getPublicDir()
    {
        return $this->_pubDirectory;
    }

    /**
     * Update required parameters with default values if custom not specified
     *
     * @param array &$params
     * @return $this
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function updateDesignParams(array &$params)
    {
        $defaults = $this->_design->getDesignParams();

        // Set area
        if (empty($params['area'])) {
            $params['area'] = $defaults['area'];
        }

        // Set themeModel
        $theme = null;
        $area = $params['area'];
        if (!empty($params['themeId'])) {
            $theme = $params['themeId'];
        } elseif (isset($params['theme'])) {
            $theme = $params['theme'];
        } elseif (empty($params['themeModel']) && $area !== $defaults['area']) {
            $theme = $this->_design->getConfigurationDesignTheme($area);
        }

        if ($theme) {
            $params['themeModel'] = $this->themeFactory->create($theme, $area);
        } elseif (empty($params['themeModel'])) {
            $params['themeModel'] = $defaults['themeModel'];
        }


        // Set module
        if (!array_key_exists('module', $params)) {
            $params['module'] = false;
        }

        // Set locale
        if (empty($params['locale'])) {
            $params['locale'] = $defaults['locale'];
        }
        return $this;
    }
}
