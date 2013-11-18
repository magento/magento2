<?php
/**
 * Rule for searching php file dependency
 *
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
 * @category    Magento
 * @package     Magento
 * @subpackage  static_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\TestFramework\Dependency;

class PhpRule implements \Magento\TestFramework\Dependency\RuleInterface
{
    /**
     * Gets alien dependencies information for current module by analyzing file's contents
     *
     * @param string $currentModule
     * @param string $fileType
     * @param string $file
     * @param string $contents
     * @return array
     */
    public function getDependencyInfo($currentModule, $fileType, $file, &$contents)
    {
        if (!in_array($fileType, array('php'))) {
            return array();
        }

        $pattern = '~\b(?<class>(?<module>(' . implode('_|',
                \Magento\TestFramework\Utility\Files::init()->getNamespaces()) .
                '[_\\\\])[a-zA-Z0-9]+)[a-zA-Z0-9_\\\\]*)\b~';

        $dependenciesInfo = array();
        if (preg_match_all($pattern, $contents, $matches)) {
            $matches['module'] = array_unique($matches['module']);
            foreach ($matches['module'] as $i => $referenceModule) {
                $referenceModule = str_replace('_', '\\', $referenceModule);
                if ($currentModule == $referenceModule || $referenceModule == 'Magento\MagentoException') {
                    continue;
                }
                $dependenciesInfo[] = array(
                    'module' => $referenceModule,
                    'type'   => \Magento\TestFramework\Dependency\RuleInterface::TYPE_HARD,
                    'source' => trim($matches['class'][$i]),
                );
            }
        }
        return $dependenciesInfo;
    }
}
