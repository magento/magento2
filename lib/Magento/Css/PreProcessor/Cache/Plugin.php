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

namespace Magento\Css\PreProcessor\Cache;

use Magento\Filesystem;
use Magento\Css\PreProcessor\Cache;

/**
 * Plugin for less caching
 */
class Plugin
{
    /**
     * @var CacheManagerFactory
     */
    protected $cacheManagerFactory;

    /**
     * @var \Magento\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Css\PreProcessor\Cache\CacheManager
     */
    protected $cacheManager;

    /**
     * @param CacheManagerFactory $cacheManagerFactory
     * @param \Magento\Logger $logger
     */
    public function __construct(
        CacheManagerFactory $cacheManagerFactory,
        \Magento\Logger $logger
    ) {
        $this->cacheManagerFactory = $cacheManagerFactory;
        $this->logger = $logger;
    }

    /**
     * @param array $arguments
     * @param \Magento\Code\Plugin\InvocationChain $invocationChain
     * @return string|null
     */
    public function aroundProcess(array $arguments, \Magento\Code\Plugin\InvocationChain $invocationChain)
    {
        // check if source path already exist
        if (isset($arguments[3])) {
            return $invocationChain->proceed($arguments);
        }

        $this->initializeCacheManager($arguments[0], $arguments[1]);

        $cachedFile = $this->cacheManager->getCachedFile();
        if (null !== $cachedFile) {
            return $cachedFile;
        }

        try {
            $result = $invocationChain->proceed($arguments);
            $this->cacheManager->saveCache($result);
        } catch (Filesystem\FilesystemException $e) {
            $this->logger->logException($e);
            return null;
        }
        return $result;
    }

    /**
     * @param string $lessFilePath
     * @param array $params
     * @return $this
     */
    protected function initializeCacheManager($lessFilePath, $params)
    {
        $this->cacheManager = $this->cacheManagerFactory->create($lessFilePath, $params);
        return $this;
    }

    /**
     * @param array $arguments
     * @return array
     */
    public function beforeProcessLessInstructions(array $arguments)
    {
        if (null !== $this->cacheManager) {
            list($lessFilePath, $params) = $arguments;
            $this->cacheManager->addEntityToCache($lessFilePath, $params);
        }
        return $arguments;
    }
}
