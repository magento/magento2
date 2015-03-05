<?php
/**
 * {license_notice}
 *
 * @copyright  {copyright}
 * @license    {license_link}
 */

namespace Magento\Tools\Webdev;

use Magento\Framework\Test\Utility\Files;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\App\View\Deployment\Version;

/**
 * A service for Collecting less sources
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collector
{
    /**
     * @var \Magento\Framework\Less\FileGenerator
     */
    protected $fileGenerator;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /** @var ObjectManagerFactory */
    private $omFactory;

    /** @var \Magento\Tools\View\Deployer\Log */
    private $logger;

    /** @var \Magento\Framework\View\Asset\Repository */
    private $assetRepo;

    /** @var \Magento\Framework\View\Asset\Source */
    private $assetSource;

    /** @var \Magento\Framework\App\View\Asset\Publisher */
    private $assetPublisher;

    /**
     * @param \Magento\Framework\View\Asset\Source $assetSource
     * @param \Magento\Framework\App\State $_appState
     * @param \Magento\Tools\View\Deployer\Log $logger
     * @param \Magento\Framework\Less\FileGenerator $fileGenerator
     */
    public function __construct(
        \Magento\Framework\View\Asset\Source $assetSource,
        \Magento\Framework\App\State $_appState,
        \Magento\Tools\View\Deployer\Log $logger,
        \Magento\Framework\Less\FileGenerator $fileGenerator
    ) {
        $this->logger = $logger;
        $this->fileGenerator = $fileGenerator;
        $this->assetSource = $assetSource;
        $this->_appState = $_appState;
    }

    /**
     * @param ObjectManagerFactory $omFactory
     * @param $locale
     * @param $area
     * @param $theme
     * @param array $files
     */
    public function tree(ObjectManagerFactory $omFactory, $locale, $area, $theme, array $files)
    {
        $this->omFactory = $omFactory;
        $this->logger->logMessage("Gathering {$area}/{$locale}/{$theme} sources.");
        $this->emulateApplicationArea($area);
        $this->_appState->setAreaCode($area);
        foreach ($files as $file ) {
            $file .= '.less';
            $this->logger->logMessage("Gathering {$file} sources.");
            $asset = $this->assetRepo->createAsset(
                $file,
                ['area' => $area, 'theme' => $theme, 'locale' => $locale]
            );
            $sourceFile = $this->assetSource->findSource($asset);
            $content = file_get_contents($sourceFile);
            $chain = new \Magento\Framework\View\Asset\PreProcessor\Chain(
                $asset,
                $content,
                'less'
            );
            $this->fileGenerator->generateLessFileTree($chain);
            $this->logger->logMessage("Done");
        }
        return;
    }

    /**
     * Emulate application area and various services that are necessary for populating files
     *
     * @param string $areaCode
     * @return void
     */
    private function emulateApplicationArea($areaCode)
    {
        $objectManager = $this->omFactory->create(
            [\Magento\Framework\App\State::PARAM_MODE => \Magento\Framework\App\State::MODE_DEFAULT]
        );
        /** @var \Magento\Framework\App\State $appState */
        $appState = $objectManager->get('Magento\Framework\App\State');
        $appState->setAreaCode($areaCode);
        /** @var \Magento\Framework\App\ObjectManager\ConfigLoader $configLoader */
        $configLoader = $objectManager->get('Magento\Framework\App\ObjectManager\ConfigLoader');
        $objectManager->configure($configLoader->load($areaCode));
        $this->assetRepo = $objectManager->get('Magento\Framework\View\Asset\Repository');
        $this->assetPublisher = $objectManager->get('Magento\Framework\App\View\Asset\Publisher');
    }

}
