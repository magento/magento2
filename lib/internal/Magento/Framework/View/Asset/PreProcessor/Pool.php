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

namespace Magento\Framework\View\Asset\PreProcessor;

use Magento\Framework\ObjectManager;

/**
 * A registry of asset preprocessors (not to confuse with the "Registry" pattern)
 */
class Pool
{
    /**
     * @var \Magento\Framework\ObjectManager
     */
    private $objectManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
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
        $result = array();
        if ($sourceContentType == 'less') {
            if ($targetContentType == 'css') {
                $result[] = $this->objectManager->get('Magento\Framework\Css\PreProcessor\Less');
            } else if ($targetContentType == 'less') {
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
