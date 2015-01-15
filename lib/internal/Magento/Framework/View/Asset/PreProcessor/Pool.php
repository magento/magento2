<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\PreProcessor;

use Magento\Framework\ObjectManagerInterface;

/**
 * A registry of asset preprocessors (not to confuse with the "Registry" pattern)
 */
class Pool
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Retrieve preprocessors instances suitable to convert source content type into a destination one
     *
     * BUG: this implementation is hard-coded intentionally because there is a logic duplication that needs to be fixed.
     * Adding an extensibility layer through DI configuration would add even more fragility to this design.
     * If you need to add another preprocessor, use interceptors or class inheritance (at your own risk).
     *
     * @param string $sourceContentType
     * @param string $targetContentType
     * @return \Magento\Framework\View\Asset\PreProcessorInterface[]
     */
    public function getPreProcessors($sourceContentType, $targetContentType)
    {
        $result = [];
        if ($sourceContentType == 'less') {
            if ($targetContentType == 'css') {
                $result[] = $this->objectManager->get('Magento\Framework\Css\PreProcessor\Less');
            } elseif ($targetContentType == 'less') {
                /**
                 * @bug This logic is duplicated at \Magento\Framework\Less\FileGenerator::generateLessFileTree()
                 * If you need to extend or modify behavior of LESS preprocessing, you must account for both places
                 */
                $result[] = $this->objectManager->get('Magento\Framework\Less\PreProcessor\Instruction\MagentoImport');
                $result[] = $this->objectManager->get('Magento\Framework\Less\PreProcessor\Instruction\Import');
            }
        }
        if ($targetContentType == 'css') {
            $result[] = $this->objectManager->get('Magento\Framework\View\Asset\PreProcessor\ModuleNotation');
        }
        return $result;
    }
}
