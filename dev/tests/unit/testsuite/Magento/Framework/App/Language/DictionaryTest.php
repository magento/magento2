<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Language;

use Magento\Framework\App\Filesystem\DirectoryList;

class DictionaryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Language\Dictionary
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $dir;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configFactory;

    protected function setUp()
    {
        $this->dir = $this->getMockForAbstractClass('\Magento\Framework\Filesystem\Directory\ReadInterface');
        $filesystem = $this->getMock('\Magento\Framework\Filesystem', [], [], '', false);
        $filesystem->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::LOCALE)
            ->will($this->returnValue($this->dir));
        $this->configFactory = $this->getMockBuilder('\Magento\Framework\App\Language\ConfigFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new Dictionary($filesystem, $this->configFactory);
    }

    /**
     * @param array $languagesData
     * @param array $csvMap
     * @param array $dictionaryMap
     * @param $languageCode
     * @param array $expectation
     * @dataProvider dictionaryDataProvider
     */
    public function testDictionaryGetter($languagesData, $csvMap, $dictionaryMap, $languageCode, $expectation)
    {
        $languagePaths = array_keys($languagesData);
        $this->dir->expects($this->any())->method('search')->will($this->returnValueMap(
            array_merge([['*/*/language.xml', null, $languagePaths]], $csvMap)
        ));

        // Return first argument to mark content for configuration factory mock
        $this->dir->expects($this->any())->method('readFile')->will($this->returnArgument(0));
        $configCallback = $this->returnCallback(function ($arguments) use ($languagesData) {
            return $this->getLanguageConfigMock($languagesData[$arguments['source']]);
        });
        $this->configFactory->expects($this->any())->method('create')->will($configCallback);

        // Covers data from dataProvider
        $dictionaryMap = array_map(function ($data) {
            list($path, $result) = $data;
            return [$path, $this->getCsvMock($result)];
        }, $dictionaryMap);
        $this->dir->expects($this->any())->method('openFile')->will($this->returnValueMap($dictionaryMap));

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
     * Create mock of language configuration model
     *
     * @param array $languageData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getLanguageConfigMock($languageData)
    {
        $languageConfig = $this->getMock('\Magento\Framework\App\Language\Config', [], [], '', false);
        $languageConfig->expects($this->any())->method('getCode')->will($this->returnValue($languageData['code']));
        $languageConfig->expects($this->any())->method('getVendor')->will($this->returnValue($languageData['vendor']));
        $languageConfig->expects($this->any())->method('getPackage')
            ->will($this->returnValue($languageData['package']));
        $languageConfig->expects($this->any())->method('getSortOrder')
            ->will($this->returnValue($languageData['sort_order']));
        $languageConfig->expects($this->any())->method('getUses')->will($this->returnValue($languageData['use']));
        return $languageConfig;
    }

    /**
     * Imitate a CSV-file read operation through "App filesystem" interface
     *
     * @param array $data
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getCsvMock($data)
    {
        $file = $this->getMockForAbstractClass('Magento\Framework\Filesystem\File\ReadInterface');
        for ($i = 0; $i < count($data); $i++) {
            $file->expects($this->at($i))->method('readCsv')->will($this->returnValue($data[$i]));
        }
        $file->expects($this->at($i))->method('readCsv')->will($this->returnValue(false));
        return $file;
    }

    /**
     * @return array
     */
    private function getDataMultipleInheritance()
    {
        return [
            'languages' => [
                'foo/en_au/language.xml' => [
                    'code' => 'en_AU',
                    'vendor' => 'foo',
                    'package' => 'en_au',
                    'sort_order' => 0,
                    'use' => [
                        ['vendor' => 'bar', 'package' => 'en_gb'],
                        ['vendor' => 'baz', 'package' => 'en_gb'],
                    ],
                ],
                'bar/en_gb/language.xml' => [
                    'code' => 'en_GB',
                    'vendor' => 'bar',
                    'package' => 'en_gb',
                    'sort_order' => 100,
                    'use' => [
                        ['vendor' => 'bar', 'package' => 'en_us'],
                    ],
                ],
                'baz/en_gb/language.xml' => [
                    'code' => 'en_GB',
                    'vendor' => 'baz',
                    'package' => 'en_gb',
                    'sort_order' => 50,
                    'use' => [],
                ],
                'bar/en_us/language.xml' => [
                    'code' => 'en_US',
                    'vendor' => 'bar',
                    'package' => 'en_us',
                    'sort_order' => 0,
                    'use' => [],
                ],
            ],
            // ValueMap for \Magento\Framework\Filesystem\Directory\ReadInterface::search($pattern, $path = null)
            'csv_map' => [
                ['bar/en_us/*.csv', null, ['bar/en_us/b.csv', 'bar/en_us/a.csv']],
                ['baz/en_gb/*.csv', null, ['baz/en_gb/1.csv']],
                ['bar/en_gb/*.csv', null, ['bar/en_gb/1.csv']],
                ['foo/en_au/*.csv', null, ['foo/en_au/1.csv', 'foo/en_au/2.csv']],
            ],
            // ValueMap for \Magento\Framework\Filesystem\Directory\ReadInterface::openFile($path)
            'dictionary_map' => [
                ['bar/en_us/a.csv', [['one', '1'], ['two', '2']]],
                ['bar/en_us/b.csv', [['three', '3'], ['four', '4']]],
                ['baz/en_gb/1.csv', [['four and 5/10', '4.5']]],
                ['bar/en_gb/1.csv', [['four and 75/100', '4.75'], ['four and 5/10', '4.50']]],
                ['foo/en_au/1.csv', [['one', '1.0'], ['five', '5.0']]],
                ['foo/en_au/2.csv', [['six', '6.0']]],
            ],
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
            'languages' => [
                'theirs/ru_ru/language.xml' => [
                    'code' => 'ru_RU',
                    'vendor' => 'theirs',
                    'package' => 'ru_ru',
                    'sort_order' => 0,
                    'use' => [],
                ],
                'my/ru_ru/language.xml' => [
                    'code' => 'ru_RU',
                    'vendor' => 'my',
                    'package' => 'ru_ru',
                    'sort_order' => 100,
                    'use' => [
                        ['vendor' => 'theirs', 'package' => 'ru_ru'],
                    ],
                ],
            ],
            // ValueMap for \Magento\Framework\Filesystem\Directory\ReadInterface::search($pattern, $path = null)
            'csv_map' => [
                ['theirs/ru_ru/*.csv', null, ['theirs/ru_ru/1.csv']],
                ['my/ru_ru/*.csv', null, ['my/ru_ru/1.csv']],
            ],
            // ValueMap for \Magento\Framework\Filesystem\Directory\ReadInterface::openFile($path)
            'dictionary_map' => [
                ['theirs/ru_ru/1.csv', [['one', '1'], ['two', '2']]],
                ['my/ru_ru/1.csv', [['three', '3'], ['one', '1.0']]],
            ],
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
            'languages' => [
                'first/en_us/language.xml' => [
                    'code' => 'en_US',
                    'vendor' => 'first',
                    'package' => 'en_us',
                    'sort_order' => 0,
                    'use' => [
                        ['vendor' => 'second', 'package' => 'en_gb'],
                    ],
                ],
                'second/en_gb/language.xml' => [
                    'code' => 'en_GB',
                    'vendor' => 'second',
                    'package' => 'en_gb',
                    'sort_order' => 0,
                    'use' => [
                        ['vendor' => 'first', 'package' => 'en_us'],
                    ],
                ],
            ],
            // ValueMap for \Magento\Framework\Filesystem\Directory\ReadInterface::search($pattern, $path = null)
            'csv_map' => [
                ['first/en_us/*.csv', null, ['first/en_us/1.csv']],
                ['second/en_gb/*.csv', null, ['second/en_gb/1.csv']],
            ],
            // ValueMap for \Magento\Framework\Filesystem\Directory\ReadInterface::openFile($path)
            'dictionary_map' => [
                ['first/en_us/1.csv', [['three', '3'], ['one', '1.0']]],
                ['second/en_gb/1.csv', [['one', '1'], ['two', '2']]],
            ],
            // Dictionary that will be requested
            'language_code' => 'en_US',
            // Expected merged dictionary data
            'expectation' => [
                'one' => '1.0',
                'two' => '2',
                'three' => '3',
            ]
        ];
    }
}
