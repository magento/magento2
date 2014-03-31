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
 * @category   Tools
 * @package    view
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tools\View\Generator;

/**
 * Transformation of files, which must be copied to new location and its contents processed
 */
class ThemeDeployment
{
    /**
     * Helper to process CSS content and fix urls
     *
     * @var \Magento\View\Url\CssResolver
     */
    private $_cssUrlResolver;

    /**
     * Destination dir, where files will be copied to
     *
     * @var string
     */
    private $_destinationHomeDir;

    /**
     * List of extensions for files, which should be deployed.
     * For efficiency it is a map of ext => ext, so lookup by hash is possible.
     *
     * @var array
     */
    private $_permitted = array();

    /**
     * List of extensions for files, which must not be deployed
     * For efficiency it is a map of ext => ext, so lookup by hash is possible.
     *
     * @var array
     */
    private $_forbidden = array();

    /**
     * Whether to actually do anything inside the filesystem
     *
     * @var bool
     */
    private $_isDryRun;

    /**
     * @var \Magento\App\State
     */
    private $appState;

    /**
     * @var \Magento\View\Asset\PreProcessor\PreProcessorInterface
     */
    private $preProcessor;

    /**
     * @var \Magento\View\Publisher\FileFactory
     */
    private $fileFactory;

    /**
     * @var \Magento\Filesystem\Directory\WriteInterface
     */
    private $tmpDirectory;

    /**
     * Constructor
     *
     * @param \Magento\View\Url\CssResolver $cssUrlResolver
     * @param \Magento\App\Filesystem $filesystem
     * @param \Magento\View\Asset\PreProcessor\PreProcessorInterface $preProcessor
     * @param \Magento\View\Publisher\FileFactory $fileFactory
     * @param \Magento\App\State $appState
     * @param \Magento\Core\Model\Theme\DataFactory $themeFactory
     * @param string $destinationHomeDir
     * @param string $configPermitted
     * @param string|null $configForbidden
     * @param bool $isDryRun
     * @throws \Magento\Exception
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\View\Url\CssResolver $cssUrlResolver,
        \Magento\App\Filesystem $filesystem,
        \Magento\View\Asset\PreProcessor\PreProcessorInterface $preProcessor,
        \Magento\View\Publisher\FileFactory $fileFactory,
        \Magento\App\State $appState,
        \Magento\Core\Model\Theme\DataFactory $themeFactory,
        $destinationHomeDir,
        $configPermitted,
        $configForbidden = null,
        $isDryRun = false
    ) {
        $this->themeFactory = $themeFactory;
        $this->appState = $appState;
        $this->preProcessor = $preProcessor;
        $this->tmpDirectory = $filesystem->getDirectoryWrite(\Magento\App\Filesystem::VAR_DIR);
        $this->fileFactory = $fileFactory;
        $this->_cssUrlResolver = $cssUrlResolver;
        $this->_destinationHomeDir = $destinationHomeDir;
        $this->_isDryRun = $isDryRun;
        $this->_permitted = $this->_loadConfig($configPermitted);
        if ($configForbidden) {
            $this->_forbidden = $this->_loadConfig($configForbidden);
        }
        $conflicts = array_intersect($this->_permitted, $this->_forbidden);
        if ($conflicts) {
            $message = 'Conflicts: the following extensions are added both to permitted and forbidden lists: %s';
            throw new \Magento\Exception(sprintf($message, implode(', ', $conflicts)));
        }
    }

    /**
     * Load config with file extensions
     *
     * @param string $path
     * @return array
     * @throws \Magento\Exception
     */
    protected function _loadConfig($path)
    {
        if (!file_exists($path)) {
            throw new \Magento\Exception("Config file does not exist: {$path}");
        }

        $contents = include $path;
        $contents = array_unique($contents);
        $contents = array_map('strtolower', $contents);
        $contents = $contents ? array_combine($contents, $contents) : array();
        return $contents;
    }

    /**
     * Copy all the files according to $copyRules
     *
     * @param array $copyRules
     * @return void
     */
    public function run($copyRules)
    {
        foreach ($copyRules as $copyRule) {
            $destinationContext = $copyRule['destinationContext'];
            $context = array('source' => $copyRule['source'], 'destinationContext' => $destinationContext);

            $destDir = \Magento\View\DeployedFilesManager::buildDeployedFilePath(
                $destinationContext['area'],
                $destinationContext['themePath'],
                '',
                $destinationContext['module']
            );
            $destDir = rtrim($destDir, '\\/');

            $this->_copyDirStructure($copyRule['source'], $this->_destinationHomeDir . '/' . $destDir, $context);
        }
    }

    /**
     * Copy dir structure and files from $sourceDir to $destinationDir
     *
     * @param string $sourceDir
     * @param string $destinationDir
     * @param array $context
     * @return void
     * @throws \Magento\Exception
     */
    protected function _copyDirStructure($sourceDir, $destinationDir, $context)
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($files as $fileSource) {
            $fileSource = (string)$fileSource;
            $extension = strtolower(pathinfo($fileSource, PATHINFO_EXTENSION));
            if ($extension == 'less') {
                $fileSource = preg_replace('/\.less$/', '.css', $fileSource);
            }
            $localPath = substr($fileSource, strlen($sourceDir) + 1);
            $themeModel = $this->themeFactory->create(
                array(
                    'data' => array(
                        'theme_path' => $context['destinationContext']['themePath'],
                        'area' => $context['destinationContext']['area']
                    )
                )
            );
            $fileObject = $this->fileFactory->create(
                $localPath,
                array_merge($context['destinationContext'], array('themeModel' => $themeModel)),
                $fileSource
            );
            /** @var \Magento\View\Publisher\FileAbstract $fileObject */
            $fileObject = $this->appState->emulateAreaCode(
                $context['destinationContext']['area'],
                array($this->preProcessor, 'process'),
                array($fileObject, $this->tmpDirectory)
            );

            if ($fileObject->getSourcePath()) {
                $fileSource = $fileObject->getSourcePath();
            }

            if (isset($this->_forbidden[$extension])) {
                continue;
            }

            if (!isset($this->_permitted[$extension])) {
                $message = sprintf(
                    'The file extension "%s" must be added either to the permitted or forbidden list. File: %s',
                    $extension,
                    $fileSource
                );
                throw new \Magento\Exception($message);
            }

            if (file_exists($fileSource)) {
                $fileDestination = $destinationDir . '/' . $localPath;
                $this->_deployFile($fileSource, $fileDestination, $context);
            }
        }
    }

    /**
     * Deploy file to the destination path, also processing modular paths inside css-files.
     *
     * @param string $fileSource
     * @param string $fileDestination
     * @param array $context
     * @return void
     * @throws \Magento\Exception
     */
    protected function _deployFile($fileSource, $fileDestination, $context)
    {
        // Create directory
        $destFileDir = dirname($fileDestination);
        if (!is_dir($destFileDir) && !$this->_isDryRun) {
            mkdir($destFileDir, 0777, true);
        }

        // Copy file
        $extension = pathinfo($fileSource, PATHINFO_EXTENSION);
        if (strtolower($extension) == 'css') {
            // For CSS files we need to process content and fix urls
            // Callback to resolve relative urls to the file names
            $destContext = $context['destinationContext'];
            $destHomeDir = $this->_destinationHomeDir;
            $callback = function ($relativeUrl) use ($destContext, $destFileDir, $destHomeDir) {
                $parts = explode(\Magento\View\Service::SCOPE_SEPARATOR, $relativeUrl);
                if (count($parts) == 2) {
                    list($module, $file) = $parts;
                    if (!strlen($module) || !strlen($file)) {
                        throw new \Magento\Exception("Wrong module url: {$relativeUrl}");
                    }
                    $relPath = \Magento\View\DeployedFilesManager::buildDeployedFilePath(
                        $destContext['area'],
                        $destContext['themePath'],
                        $file,
                        $module
                    );

                    $result = $destHomeDir . '/' . $relPath;
                } else {
                    $result = $destFileDir . '/' . $relativeUrl;
                }
                return $result;
            };

            // Replace relative urls and write the modified content (if not dry run)
            $content = file_get_contents($fileSource);
            $content = $this->_cssUrlResolver->replaceCssRelativeUrls(
                $content,
                $fileSource,
                $fileDestination,
                $callback
            );

            if (!$this->_isDryRun) {
                file_put_contents($fileDestination, $content);
            }
        } else {
            if (!$this->_isDryRun) {
                copy($fileSource, $fileDestination);
            }
        }
    }
}
