<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Language;

use Magento\TestFramework\Helper\Bootstrap;

class DictionaryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

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

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->directoryFactory = $this->objectManager->create(
            \Magento\Framework\Filesystem\Directory\ReadFactory::class
        );
        $this->configFactory = $this->objectManager->create(\Magento\Framework\App\Language\ConfigFactory::class);
    }

    /**
     * @param string $languageCode
     * @param array $expectation
     * @dataProvider dictionaryDataProvider
     * @magentoComponentsDir Magento/Framework/App/Language/_files
     */
    public function testDictionaryGetter($languageCode, $expectation)
    {
        $this->model = $this->objectManager->create(
            \Magento\Framework\App\Language\Dictionary::class,
            ['directoryReadFactory' => $this->directoryFactory, 'configFactory' => $this->configFactory]
        );
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
            'a case with circular inheritance' => $this->getDataCircularInheritance(),
            // Fourth case with multiple inheritance from dev docs
            'a case with multiple inheritance from dev docs' => $this->getDataMultipleInheritanceFromDevDocs()
        ];
    }

    /**
     * @return array
     */
    private function getDataMultipleInheritance()
    {
        return [
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
        return [
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
        return [
            // Dictionary that will be requested
            'language_code' => 'en_AZ',
            // Expected merged dictionary data
            'expectation' => [
                'one' => '1.0',
                'two' => '2',
                'three' => '3',
                'four' => '4',
            ]
        ];
    }

    /**
     * If a language package inherits from two packages:
     * ...
     *  <code>en_AK</code>
     * ...
     *  <use vendor="parent-package-one" package="language_package_one"/>
     *  <use vendor= "parent-package-two" package="language_package_two"/>
     * ...
     *
     * In the preceding example:
     *  language_package_one inherits from en_au_package and en_au_package inherits from en_ie_package
     *  language_package_two inherits from en_ca_package and en_ca_package inherits from en_us_package
     *
     * If the Magento application cannot find word or phrase in the en_AK package,
     * it looks in other packages in following sequence:
     *  parent-package-one/language_package_one
     *  <vendorname>/en_au_package
     *  <vendorname>/en_ie_package
     *  parent-package-two/language_package_two
     *  <vendorname>/en_ca_package
     *  <vendorname>/en_us_package
     *
     * @return array
     */
    private function getDataMultipleInheritanceFromDevDocs()
    {
        return [
            // Dictionary that will be requested
            'language_code' => 'en_AK',
            // Expected merged dictionary data
            'expectation' => [
                'one' => 'en_us_package_one',
                'two' => 'en_ca_package_two',
                'three' => 'language_package_two_three',
                'four' => 'en_ie_package_four',
                'five' => 'en_au_package_five',
                'six' => 'language_package_one_six',
                'seven' => 'en_ak_seven',
                'eight' => 'en_ak_eight',
                'nine' => 'en_ak_nine',
            ]
        ];
    }
}
