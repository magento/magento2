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

namespace Magento\Test\Integrity\App\Language;

use \Magento\Framework\App\Language\Config;

class CircularDependencyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config[][]
     */
    private $packs;

    /**
     * Test circular dependencies between languages
     */
    public function testCircularDependencies()
    {
        $package = new Package();
        $rootDirectory = \Magento\TestFramework\Utility\Files::init()->getPathToSource();
        $declaredLanguages = $package->readDeclarationFiles($rootDirectory);
        $packs = [];
        foreach ($declaredLanguages as $language) {
            $filePath = reset($language);
            $languageConfig = new Config(file_get_contents($filePath));
            $this->packs[$languageConfig->getVendor()][$languageConfig->getPackage()] = $languageConfig;
            $packs[] = $languageConfig;
        }

        /** @var $languageConfig Config */
        foreach ($packs as $languageConfig) {
            $languages = [];
            /** @var $config Config */
            foreach ($this->collectCircularInheritance($languageConfig) as $config) {
                $languages[] = $config->getVendor() . '/' . $config->getPackage();
            }
            if (!empty($languages)) {
                $this->fail("Circular dependency detected:\n" . implode(' -> ', $languages));
            }
        }
    }

    /**
     * @param Config $languageConfig
     * @param array $languageList
     * @param bool $isCircular
     * @return array|null
     */
    private function collectCircularInheritance(Config $languageConfig, &$languageList = [], &$isCircular = false)
    {
        $packKey = implode('|', [$languageConfig->getVendor(), $languageConfig->getPackage()]);
        if (isset($languageList[$packKey])) {
            $isCircular = true;
        } else {
            $languageList[$packKey] = $languageConfig;
            foreach ($languageConfig->getUses() as $reuse) {
                if (isset($this->packs[$reuse['vendor']][$reuse['package']])) {
                    $this->collectCircularInheritance(
                        $this->packs[$reuse['vendor']][$reuse['package']],
                        $languageList,
                        $isCircular
                    );
                }
            }
        }
        return $isCircular ? $languageList : [];
    }
}
