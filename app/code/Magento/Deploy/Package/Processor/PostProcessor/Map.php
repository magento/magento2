<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Package\Processor\PostProcessor;

use Magento\Deploy\Package\PackageFile;
use Magento\Deploy\Service\DeployStaticFile;
use Magento\Deploy\Package\Package;
use Magento\Deploy\Package\PackageFileFactory;
use Magento\Framework\App\DeploymentConfig\Writer\PhpFormatter;
use Magento\Deploy\Package\Processor\ProcessorInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Asset\Minification;
use Magento\Framework\View\Asset\RepositoryMap;

/**
 * Map post-processor is for generating map files for JavaScript and server-side URL resolvers
 *
 * Map files needed only for compact deployment, and yet can be used for tracking changes of deployed static files
 * in development mode, when using symlinks is not possible
 * @since 2.2.0
 */
class Map implements ProcessorInterface
{
    /**
     * Service class is used for deploying files to public directory
     *
     * The service does simple write, copy or go through publication process (apply fallback rules, pre-processing etc)
     *
     * @var DeployStaticFile
     * @since 2.2.0
     */
    private $deployStaticFile;

    /**
     * PHP code formatter
     *
     * Formatter generates code for PHP file that returns data array
     *
     * @var PhpFormatter
     * @since 2.2.0
     */
    private $formatter;

    /**
     * Factory class for deployment package object
     *
     * @see PackageFile
     * @var PackageFileFactory
     * @since 2.2.0
     */
    private $packageFileFactory;

    /**
     * Helper class for static files minification related processes
     *
     * @var Minification
     * @since 2.2.0
     */
    private $minification;

    /**
     * Deployment procedure options
     *
     * @var array
     * @since 2.2.0
     */
    private $options = [];

    /**
     * Map constructor
     *
     * @param DeployStaticFile $deployStaticFile
     * @param PhpFormatter $formatter
     * @param PackageFileFactory $packageFileFactory
     * @param Minification $minification
     * @since 2.2.0
     */
    public function __construct(
        DeployStaticFile $deployStaticFile,
        PhpFormatter $formatter,
        PackageFileFactory $packageFileFactory,
        Minification $minification
    ) {
        $this->deployStaticFile = $deployStaticFile;
        $this->packageFileFactory = $packageFileFactory;
        $this->formatter = $formatter;
        $this->minification = $minification;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function process(Package $package, array $options)
    {
        if ($package->isVirtual()) {
            return true;
        }

        // delete existing map files
        $this->deployStaticFile->deleteFile($package->getPath() . '/' . RepositoryMap::MAP_NAME);
        $this->deployStaticFile->deleteFile($package->getPath() . '/' . RepositoryMap::REQUIRE_JS_MAP_NAME);
        $this->deployStaticFile->deleteFile($package->getPath() . '/' . RepositoryMap::RESULT_MAP_NAME);

        if (!$package->getParam('build_map')) {
            return true;
        }

        $this->options = $options;

        $packageMap = $package->getParentMap();
        if ($packageMap) {
            $this->deployStaticFile->writeFile(
                RepositoryMap::MAP_NAME,
                $package->getPath(),
                json_encode($packageMap)
            );
            $this->deployStaticFile->writeFile(
                $this->minification->addMinifiedSign(RepositoryMap::REQUIRE_JS_MAP_NAME),
                $package->getPath(),
                $this->getRequireJsMap($packageMap)
            );
        }

        $resultMap = $package->getResultMap();
        if ($resultMap) {
            $this->deployStaticFile->writeFile(
                RepositoryMap::RESULT_MAP_NAME,
                $package->getPath(),
                json_encode($resultMap)
            );
        }

        return true;
    }

    /**
     * Retrieve require js map
     *
     * @param array $map
     * @return string
     * @since 2.2.0
     */
    private function getRequireJsMap(array $map)
    {
        $jsonMap = [];
        foreach ($map as $fileId => $fileInfo) {
            if (!in_array(pathinfo($fileId, PATHINFO_EXTENSION), ['js', 'html'])) {
                continue;
            }

            $fileId = '/' . $fileId; // add leading slash to match exclude patterns
            $filePath = $this->minification->addMinifiedSign(str_replace(Repository::FILE_ID_SEPARATOR, '/', $fileId));
            $filePath = substr($filePath, 1); // and remove
            $jsonMap[$filePath] = '../../../../'
                . $fileInfo['area'] . '/' . $fileInfo['theme'] . '/' . $fileInfo['locale'] . '/';
        }
        $jsonMap = json_encode($jsonMap);
        return "require.config({\"config\": {\"baseUrlInterceptor\":{$jsonMap}}});";
    }
}
