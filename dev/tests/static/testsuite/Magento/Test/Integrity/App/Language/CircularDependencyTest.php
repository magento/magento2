<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Integrity\App\Language;

use Magento\Framework\App\Language\Config;
use Magento\Framework\Component\ComponentRegistrar;

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
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $componentRegistrar = new ComponentRegistrar();
        $declaredLanguages = $componentRegistrar->getPaths(ComponentRegistrar::LANGUAGE);
        $validationStateMock = $this->getMock(
            \Magento\Framework\Config\ValidationStateInterface::class,
            [],
            [],
            '',
            false
        );
        $validationStateMock->method('isValidationRequired')
            ->willReturn(true);
        $domFactoryMock = $this->getMock(\Magento\Framework\Config\DomFactory::class, [], [], '', false);
        $domFactoryMock->expects($this->any())
            ->method('createDom')
            ->willReturnCallback(
                function ($arguments) use ($validationStateMock) {
                    return new \Magento\Framework\Config\Dom(
                        $arguments['xml'],
                        $validationStateMock,
                        [],
                        null,
                        $arguments['schemaFile']
                    );
                }
            );

        $packs = [];
        foreach ($declaredLanguages as $language) {
            $languageConfig = $objectManager->getObject(
                \Magento\Framework\App\Language\Config::class,
                [
                    'source' => file_get_contents($language . '/language.xml'),
                    'domFactory' => $domFactoryMock
                ]
            );
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
