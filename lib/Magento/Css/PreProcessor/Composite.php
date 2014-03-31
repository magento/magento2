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

use Magento\View\Asset\PreProcessor\PreProcessorInterface;
use Magento\View\Asset\PreProcessorFactory;

/**
 * Css pre-processor composite
 */
class Composite implements PreProcessorInterface
{
    /**
     * Temporary directory prefix
     */
    const TMP_VIEW_DIR = 'view';

    /**
     * @var PreProcessorInterface[]
     */
    protected $preProcessors = array();

    /**
     * @var PreProcessorFactory
     */
    protected $preProcessorFactory;

    /**
     * @param PreProcessorFactory $preProcessorFactory
     * @param array $preProcessors
     */
    public function __construct(PreProcessorFactory $preProcessorFactory, array $preProcessors = array())
    {
        $this->preProcessorFactory = $preProcessorFactory;
        $this->preparePreProcessors($preProcessors);
    }

    /**
     * @param \Magento\View\Publisher\FileInterface $publisherFile
     * @param \Magento\Filesystem\Directory\WriteInterface $targetDirectory
     * @return \Magento\View\Publisher\FileInterface
     */
    public function process(\Magento\View\Publisher\FileInterface $publisherFile, $targetDirectory)
    {
        foreach ($this->preProcessors as $preProcessor) {
            $publisherFile = $preProcessor->process($publisherFile, $targetDirectory);
        }

        return $publisherFile;
    }

    /**
     * @param array $preProcessors
     * @return PreProcessorInterface[]
     */
    protected function preparePreProcessors($preProcessors)
    {
        if (empty($this->preProcessors)) {
            foreach ($preProcessors as $preProcessorClass) {
                $this->preProcessors[] = $this->preProcessorFactory->create($preProcessorClass);
            }
        }
        return $this;
    }
}
