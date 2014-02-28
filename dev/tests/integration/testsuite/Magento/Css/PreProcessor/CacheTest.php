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

namespace Magento\Css\PreProcessor;

use \Magento\Css\PreProcessor\Cache\Import\Cache;
use \Magento\Css\PreProcessor\Cache\Import\Map\Storage;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Css\PreProcessor\Less
     */
    protected $preProcessorLess;

    /**
     * @var \Magento\Filesystem
     */
    protected $filesystem;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->preProcessorLess = $this->objectManager->create('Magento\Css\PreProcessor\Less');
        $this->filesystem = $this->objectManager->get('Magento\Filesystem');

        \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize(array(
            \Magento\App\Filesystem::PARAM_APP_DIRS => array(
                \Magento\App\Filesystem::PUB_LIB_DIR => array(
                    'path' => __DIR__ . '/_files/cache/lib'
                ),
            )
        ));

        $this->clearCache();
    }

    protected function tearDown()
    {
        $this->clearCache();
    }

    public function testLessCache()
    {
        $file = $this->objectManager->create('Magento\View\Publisher\CssFile',
            [
                'filePath' => 'oyejorge.css',
                'allowDuplication' => false,
                'viewParams' => $this->getDesignParams()
            ]
        );

        $targetDirectory = $this->filesystem->getDirectoryWrite(\Magento\App\Filesystem::TMP_DIR);

        /**
         * cache was not initialize yet and return empty value
         *
         * @var \Magento\Css\PreProcessor\Cache\CacheManager $cacheManagerEmpty
         */
        $emptyCache = $this->objectManager->create('Magento\Css\PreProcessor\Cache\CacheManager');
        $emptyCache->initializeCacheByType(Cache::IMPORT_CACHE, $file);
        $this->assertEmpty($emptyCache->getCachedFile(Cache::IMPORT_CACHE));

        $this->preProcessorLess->process($file, $targetDirectory);

        /**
         * cache initialized and return cached file
         *
         * @var \Magento\Css\PreProcessor\Cache\CacheManager $cacheManagerGenerated
         */
        $generatedCache = $this->objectManager->create('Magento\Css\PreProcessor\Cache\CacheManager');
        $generatedCache->initializeCacheByType(Cache::IMPORT_CACHE, $file);
        $this->assertNotEmpty($generatedCache->getCachedFile(Cache::IMPORT_CACHE));
    }

    /**
     * @return array
     */
    protected function getDesignParams()
    {
        $designParams = ['area' => 'frontend'];
        $viewService = $this->objectManager->get('Magento\View\Service');
        $viewService->updateDesignParams($designParams);

        return $designParams;
    }

    /**
     * @return $this
     */
    protected function clearCache()
    {
        /** @var \Magento\Filesystem\Directory\WriteInterface $mapsDirectory */
        $mapsDirectory = $this->filesystem->getDirectoryWrite(\Magento\App\Filesystem::VAR_DIR);

        if ($mapsDirectory->isDirectory(Storage::MAPS_DIR)) {
            $mapsDirectory->delete(Storage::MAPS_DIR);
        }
        return $this;
    }
}
