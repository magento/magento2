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
 * Model that finds file paths by their fileId
 */
class Mage_Core_Model_View_FileSystem
{
    /**
     * Model, used to resolve the file paths
     *
     * @var Mage_Core_Model_Design_FileResolution_StrategyPool
     */
    protected $_resolutionPool = null;

    /**
     * @var Mage_Core_Model_View_Service
     */
    protected $_viewService;

    /**
     * View files system model
     *
     * @param Mage_Core_Model_Design_FileResolution_StrategyPool $resolutionPool
     * @param Mage_Core_Model_View_Service $viewService
     */
    public function __construct(
        Mage_Core_Model_Design_FileResolution_StrategyPool $resolutionPool,
        Mage_Core_Model_View_Service $viewService
    ) {
        $this->_resolutionPool = $resolutionPool;
        $this->_viewService = $viewService;
    }

    /**
     * Get existing file name with fallback to default
     *
     * @param string $fileId
     * @param array $params
     * @return string
     */
    public function getFilename($fileId, array $params = array())
    {
        $filePath = $this->_viewService->extractScope($fileId, $params);
        $this->_viewService->updateDesignParams($params);
        return $this->_resolutionPool->getFileStrategy(!empty($params['skipProxy']))
            ->getFile($params['area'], $params['themeModel'], $filePath, $params['module']);
    }

    /**
     * Get a locale file
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    public function getLocaleFileName($file, array $params = array())
    {
        $this->_viewService->updateDesignParams($params);
        $skipProxy = isset($params['skipProxy']) && $params['skipProxy'];
        return $this->_resolutionPool->getLocaleStrategy($skipProxy)->getLocaleFile($params['area'],
            $params['themeModel'], $params['locale'], $file);
    }

    /**
     * Find a view file using fallback mechanism
     *
     * @param string $fileId
     * @param array $params
     * @return string
     */
    public function getViewFile($fileId, array $params = array())
    {
        $filePath = $this->_viewService->extractScope($fileId, $params);
        $this->_viewService->updateDesignParams($params);
        $skipProxy = isset($params['skipProxy']) && $params['skipProxy'];
        return $this->_resolutionPool->getViewStrategy($skipProxy)->getViewFile($params['area'],
            $params['themeModel'], $params['locale'], $filePath, $params['module']);
    }

    /**
     * Notify that view file resolved path was changed (i.e. it was published to a public directory)
     *
     * @param string $targetPath
     * @param string $fileId
     * @param array $params
     * @return $this
     */
    public function notifyViewFileLocationChanged($targetPath, $fileId, $params)
    {
        $skipProxy = isset($params['skipProxy']) && $params['skipProxy'];
        $strategy = $this->_resolutionPool->getViewStrategy($skipProxy);
        if ($strategy instanceof Mage_Core_Model_Design_FileResolution_Strategy_View_NotifiableInterface) {
            /** @var $strategy Mage_Core_Model_Design_FileResolution_Strategy_View_NotifiableInterface  */
            $filePath = $this->_viewService->extractScope($fileId, $params);
            $this->_viewService->updateDesignParams($params);
            $strategy->setViewFilePathToMap(
                $params['area'], $params['themeModel'], $params['locale'], $params['module'], $filePath, $targetPath
            );
        }

        return $this;
    }
}
