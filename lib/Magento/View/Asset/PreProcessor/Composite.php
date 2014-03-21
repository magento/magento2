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
namespace Magento\View\Asset\PreProcessor;

use Magento\View\Asset\PreProcessorFactory;

/**
 * View asset pre-processor composite
 */
class Composite implements PreProcessorInterface
{
    /**
     * Pre-processor config
     *
     * @var array
     */
    protected $preProcessorsConfig = array();

    /**
     * Asset type pre-processor
     *
     * @var PreProcessorInterface[]
     */
    protected $assetTypePreProcessors = array();

    /**
     * Pre-processor factory
     *
     * @var \Magento\View\Asset\PreProcessorFactory
     */
    protected $preProcessorFactory;

    /**
     * Constructor
     *
     * @param PreProcessorFactory $preProcessorFactory
     * @param array $preProcessorsConfig
     */
    public function __construct(PreProcessorFactory $preProcessorFactory, array $preProcessorsConfig = array())
    {
        $this->preProcessorFactory = $preProcessorFactory;
        $this->preProcessorsConfig = $preProcessorsConfig;
    }

    /**
     * Process view asset pro-processors
     *
     * @param \Magento\View\Publisher\FileInterface $publisherFile
     * @param \Magento\Filesystem\Directory\WriteInterface $targetDirectory
     * @return \Magento\View\Publisher\FileInterface
     */
    public function process(\Magento\View\Publisher\FileInterface $publisherFile, $targetDirectory)
    {
        foreach ($this->getAssetTypePreProcessors($publisherFile->getExtension()) as $preProcessor) {
            $publisherFile = $preProcessor->process($publisherFile, $targetDirectory);
        }

        return $publisherFile;
    }

    /**
     * Get processors list for given asset type
     *
     * @param string $assetType
     * @return PreProcessorInterface[]
     */
    protected function getAssetTypePreProcessors($assetType)
    {
        if (!isset($this->assetTypePreProcessors[$assetType])) {
            $this->assetTypePreProcessors[$assetType] = array();
            foreach ($this->preProcessorsConfig as $preProcessorDetails) {
                if ($assetType === $preProcessorDetails['asset_type']) {
                    $this->assetTypePreProcessors[$assetType][] = $this->preProcessorFactory->create(
                        $preProcessorDetails['class']
                    );
                }
            }
        }
        return $this->assetTypePreProcessors[$assetType];
    }
}
