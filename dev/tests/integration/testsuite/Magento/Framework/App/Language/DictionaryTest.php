<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Language;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\TestFramework\Helper\Bootstrap;

class DictionaryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Language\Dictionary
     */
    private $model;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    private $directoryFactory;

    /**
     * @var \Magento\Framework\App\Language\ConfigFactory
     */
    private $configFactory;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->directoryFactory = $objectManager->create('Magento\Framework\Filesystem\Directory\ReadFactory');
        $this->configFactory = $objectManager->create('Magento\Framework\App\Language\ConfigFactory');
    }

    /**
     * @param $componentRegistrar
     * @param $languageCode
     * @param array $expectation
     * @dataProvider dictionaryDataProvider
     */
    public function testDictionaryGetter($componentRegistrar, $languageCode, $expectation)
    {
        $this->model = new Dictionary($this->directoryFactory, $componentRegistrar, $this->configFactory);
        $result = $this->model->getDictionary($languageCode);
        $this->assertSame($expectation, $result);
    }

    public function dictionaryDataProvider()
    {
        return [
            // First case with multiple inheritance, the obtained dictionary is en_AU
            'a case with multiple inheritance' => $this->getDataMultipleInheritance(),
            // Second case with inheritance of package with the same language code
            'a case with inheritance similar language code' => $this->getDataInheritanceWitSimilarCode(),
            // Third case with circular inheritance, when two packages depend on each other
            'a case with circular inheritance' => $this->getDataCircularInheritance()
        ];
    }

    /**
     * @return array
     */
    private function getDataMultipleInheritance()
    {
        $componentRegistrar = new ComponentRegistrar();
        ComponentRegistrar::register(ComponentRegistrar::LANGUAGE, 'bar_en_gb', __DIR__ . '/_files/bar/en_gb');
        ComponentRegistrar::register(ComponentRegistrar::LANGUAGE, 'bar_en_us', __DIR__ . '/_files/bar/en_us');
        ComponentRegistrar::register(ComponentRegistrar::LANGUAGE, 'baz_en_gb', __DIR__ . '/_files/baz/en_gb');
        ComponentRegistrar::register(ComponentRegistrar::LANGUAGE, 'foo_en_au', __DIR__ . '/_files/foo/en_au');

        return [
            'componentRegistrar' => $componentRegistrar,
            // Dictionary that will be requested
            'language_code' => 'en_AU',
            // Expected merged dictionary data
            'expectation' => [
                'one' => '1.0',
                'two' => '2',
                'three' => '3',
                'four' => '4',
                'four and 5/10' => '4.50',
                'four and 75/100' => '4.75',
                'five' => '5.0',
                'six' => '6.0',
            ]
        ];
    }

    /**
     * @return array
     */
    private function getDataInheritanceWitSimilarCode()
    {
        $componentRegistrar = new ComponentRegistrar();
        ComponentRegistrar::register(ComponentRegistrar::LANGUAGE, 'first_en_us', __DIR__ . '/_files/first/en_us');
        ComponentRegistrar::register(ComponentRegistrar::LANGUAGE, 'second_en_gb', __DIR__ . '/_files/second/en_gb');
        return [
            'componentRegistrar' => $componentRegistrar,
            // Dictionary that will be requested
            'language_code' => 'ru_RU',
            // Expected merged dictionary data
            'expectation' => [
                'one' => '1.0',
                'two' => '2',
                'three' => '3',
            ]
        ];
    }

    /**
     * @return array
     */
    private function getDataCircularInheritance()
    {
        $componentRegistrar = new ComponentRegistrar();
        ComponentRegistrar::register(ComponentRegistrar::LANGUAGE, 'my_ru_ru', __DIR__ . '/_files/my/ru_ru');
        ComponentRegistrar::register(ComponentRegistrar::LANGUAGE, 'theirs_ru_ru', __DIR__ . '/_files/theirs/ru_ru');

        return [
            'componentRegistrar' => $componentRegistrar,
            // Dictionary that will be requested
            'language_code' => 'en_US',
            // Expected merged dictionary data
            'expectation' => [
                'one' => '1',
                'two' => '2',
                'three' => '3',
                'four' => '4',
            ]
        ];
    }
}
