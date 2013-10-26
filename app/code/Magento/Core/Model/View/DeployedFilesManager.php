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
 * Builds path for files deployed into public directory in advance
 */
namespace Magento\Core\Model\View;

class DeployedFilesManager implements \Magento\Core\Model\View\PublicFilesManagerInterface
{
    /**
     * @var \Magento\Core\Model\View\Service
     */
    protected $_viewService;

    /**
     * Deployed view files manager
     *
     * @param \Magento\Core\Model\View\Service $viewService
     */
    public function __construct(\Magento\Core\Model\View\Service $viewService)
    {
        $this->_viewService = $viewService;
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicFilePath($filePath, $params)
    {
        return $this->_getDeployedFilePath($filePath, $params);
    }

    /**
     * Build a relative path to a static view file, if published with duplication.
     *
     * Just concatenates all context arguments.
     * Note: despite $locale is specified, it is currently ignored.
     *
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @param string $file
     * @param string|null $module
     * @return string
     */
    public static function buildDeployedFilePath($area, $themePath, $locale, $file, $module = null)
    {
        return $area . DIRECTORY_SEPARATOR . $themePath . DIRECTORY_SEPARATOR
            . ($module ? $module . DIRECTORY_SEPARATOR : '') . $file;
    }

    /**
     * Get deployed file path
     *
     * @param string $filePath
     * @param array $params
     * @return string
     */
    protected function _getDeployedFilePath($filePath, $params)
    {
        /** @var $themeModel \Magento\View\Design\ThemeInterface */
        $themeModel = $params['themeModel'];
        $themePath = $themeModel->getThemePath();
        while (empty($themePath) && $themeModel) {
            $themePath = $themeModel->getThemePath();
            $themeModel = $themeModel->getParentTheme();
        }
        $subPath = self::buildDeployedFilePath(
            $params['area'], $themePath, $params['locale'], $filePath, $params['module']
        );
        $deployedFilePath = $this->_viewService->getPublicDir() . DIRECTORY_SEPARATOR . $subPath;

        return $deployedFilePath;
    }
}
