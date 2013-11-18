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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Service model responsible for configuration of minified asset
 */
namespace Magento\Core\Model\Page\Asset;

class MinifyService
{
    /**#@+
     * XPaths to minification configuration
     */
    const XML_PATH_MINIFICATION_ENABLED = 'dev/%s/minify_files';
    const XML_PATH_MINIFICATION_ADAPTER = 'dev/%s/minify_adapter';
    /**#@-*/

    /**
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_storeConfig;

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var array
     */
    protected $_enabled = array();

    /**
     * @var \Magento\Code\Minifier[]
     */
    protected $_minifiers = array();

    /**
     * @var \Magento\App\Dir
     */
    protected $_dirs;

    /**
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @param \Magento\Core\Model\Store\Config $config
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\App\Dir $dirs
     * @param \Magento\App\State $appState
     */
    public function __construct(
        \Magento\Core\Model\Store\Config $config,
        \Magento\ObjectManager $objectManager,
        \Magento\App\Dir $dirs,
        \Magento\App\State $appState
    ) {
        $this->_storeConfig = $config;
        $this->_objectManager = $objectManager;
        $this->_dirs = $dirs;
        $this->_appState = $appState;
    }

    /**
     * Get filtered assets
     * Assets applicable for minification are wrapped with the minified asset
     *
     * @param array|Iterator $assets
     * @return array
     */
    public function getAssets($assets)
    {
        $resultAssets = array();
        /** @var $asset \Magento\Core\Model\Page\Asset\AssetInterface */
        foreach ($assets as $asset) {
            $contentType = $asset->getContentType();
            if ($this->_isEnabled($contentType)) {
                $asset = $this->_objectManager
                    ->create('Magento\Core\Model\Page\Asset\Minified', array(
                        'asset' => $asset,
                        'minifier' => $this->_getMinifier($contentType)
                    ));
            }
            $resultAssets[] = $asset;
        }
        return $resultAssets;
    }

    /**
     * Get minifier object configured with specified content type
     *
     * @param string $contentType
     * @return \Magento\Code\Minifier
     */
    protected function _getMinifier($contentType)
    {
        if (!isset($this->_minifiers[$contentType])) {
            $adapter = $this->_getAdapter($contentType);
            $strategyParams = array(
                'adapter' => $adapter,
            );
            switch ($this->_appState->getMode()) {
                case \Magento\App\State::MODE_PRODUCTION:
                    $strategy = $this->_objectManager->create('Magento\Code\Minifier\Strategy\Lite', $strategyParams);
                    break;
                default:
                    $strategy = $this->_objectManager
                        ->create('Magento\Code\Minifier\Strategy\Generate', $strategyParams);
            }

            $this->_minifiers[$contentType] = $this->_objectManager->create('Magento\Code\Minifier',
                array(
                    'strategy' => $strategy,
                    'baseDir' => $this->_dirs->getDir(\Magento\App\Dir::PUB_VIEW_CACHE) . '/minify',
                )
            );
        }
        return $this->_minifiers[$contentType];
    }

    /**
     * Check if minification is enabled for specified content type
     *
     * @param $contentType
     * @return bool
     */
    protected function _isEnabled($contentType)
    {
        if (!isset($this->_enabled[$contentType])) {
            $this->_enabled[$contentType] = $this->_storeConfig->getConfigFlag(
                sprintf(self::XML_PATH_MINIFICATION_ENABLED, $contentType)
            );
        }
        return $this->_enabled[$contentType];
    }

    /**
     * Get minification adapter by specified content type
     *
     * @param $contentType
     * @return mixed
     * @throws \Magento\Core\Exception
     */
    protected function _getAdapter($contentType)
    {
        $adapterClass = $this->_storeConfig->getConfig(
            sprintf(self::XML_PATH_MINIFICATION_ADAPTER, $contentType)
        );
        if (!$adapterClass) {
            throw new \Magento\Core\Exception(
                "Minification adapter is not specified for '$contentType' content type"
            );
        }

        $adapter = $this->_objectManager->create($adapterClass);
        return $adapter;
    }
}
