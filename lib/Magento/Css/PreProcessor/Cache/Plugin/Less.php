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
namespace Magento\Css\PreProcessor\Cache\Plugin;

use Magento\Filesystem;
use Magento\Css\PreProcessor\Cache\CacheManager;
use Magento\Css\PreProcessor\Cache\Import\Cache;

/**
 * Plugin for less caching
 */
class Less
{
    /**
     * @var \Magento\Logger
     */
    protected $logger;

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @param CacheManager $cacheManager
     * @param \Magento\Logger $logger
     */
    public function __construct(CacheManager $cacheManager, \Magento\Logger $logger)
    {
        $this->cacheManager = $cacheManager;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Css\PreProcessor\Less $subject
     * @param \Closure $proceed
     * @param \Magento\View\Publisher\FileInterface $publisherFile
     * @param string $targetDirectory
     *
     * @return \Magento\View\Publisher\FileInterface|null|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundProcess(
        \Magento\Css\PreProcessor\Less $subject,
        \Closure $proceed,
        \Magento\View\Publisher\FileInterface $publisherFile,
        $targetDirectory
    ) {
        if ($publisherFile->getSourcePath()) {
            return $proceed($publisherFile, $targetDirectory);
        }

        $this->cacheManager->initializeCacheByType(Cache::IMPORT_CACHE, $publisherFile);

        $cachedFile = $this->cacheManager->getCachedFile(Cache::IMPORT_CACHE);
        if ($cachedFile instanceof \Magento\View\Publisher\FileInterface) {
            return $cachedFile;
        }

        try {
            /** @var \Magento\View\Publisher\FileInterface $result */
            $result = $proceed($publisherFile, $targetDirectory);
            $this->cacheManager->saveCache(Cache::IMPORT_CACHE, $result);
        } catch (Filesystem\FilesystemException $e) {
            $this->logger->logException($e);
            return null;
        }
        return $result;
    }
}
