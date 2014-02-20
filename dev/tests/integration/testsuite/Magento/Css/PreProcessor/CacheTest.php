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

class CacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Css\PreProcessor\Less
     */
    protected $model;

    /**
     * @var \Magento\Css\PreProcessor\Cache\CacheManagerFactory
     */
    protected $cacheManagerFactory;

    /**
     * @var \Magento\App\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\View\Service
     */
    protected $viewService;

    public function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $objectManager->create('Magento\Css\PreProcessor\Less');
        $this->cacheManagerFactory = $objectManager->create('Magento\Css\PreProcessor\Cache\CacheManagerFactory');
        $this->filesystem = $objectManager->get('Magento\Filesystem');
        $this->viewService = $objectManager->get('Magento\View\Service');

        $this->clearCache();

        \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize(array(
            \Magento\App\Filesystem::PARAM_APP_DIRS => array(
                \Magento\App\Filesystem::PUB_LIB_DIR => array(
                    'path' => __DIR__ . '/_files/cache/lib'
                ),
            )
        ));
    }

    protected function tearDown()
    {
        $this->clearCache();
    }

    public function testProcess()
    {
        $sourceFilePath = 'oyejorge.less';

        $designParams = $this->getDesignParams();
        $targetDirectory = $this->filesystem->getDirectoryWrite(\Magento\App\Filesystem::TMP_DIR);

        /**
         * cache was not initialize yet and will return empty value
         *
         * @var \Magento\Css\PreProcessor\Cache\CacheManager $cacheManagerEmpty
         */
        $cacheManagerEmpty = $this->cacheManagerFactory->create($sourceFilePath, $designParams);
        $this->assertEmpty($cacheManagerEmpty->getCachedFile());

        $this->model->process($sourceFilePath, $designParams, $targetDirectory);

        /**
         * cache initialized and will return cached file
         *
         * @var \Magento\Css\PreProcessor\Cache\CacheManager $cacheManagerGenerated
         */
        $cacheManagerGenerated = $this->cacheManagerFactory->create($sourceFilePath, $designParams);
        $this->assertNotEmpty($cacheManagerGenerated->getCachedFile());
    }

    /**
     * @return array
     */
    protected function getDesignParams()
    {
        $designParams = ['area' => 'frontend'];
        /** @var \Magento\View\Service $viewService */
        $this->viewService->updateDesignParams($designParams);

        return $designParams;
    }

    /**
     * @return $this
     */
    protected function clearCache()
    {
        /** @var \Magento\Filesystem\Directory\WriteInterface $mapsDirectory */
        $mapsDirectory = $this->filesystem->getDirectoryWrite(\Magento\App\Filesystem::VAR_DIR);

        if ($mapsDirectory->isDirectory(\Magento\Css\PreProcessor\Cache\Import\Map\Storage::MAPS_DIR)) {
            $mapsDirectory->delete(\Magento\Css\PreProcessor\Cache\Import\Map\Storage::MAPS_DIR);
        }
        return $this;
    }
}
