<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\DownloadableImportExport\Test\Unit\Helper;

use Magento\DownloadableImportExport\Helper\Data as HelperData;
use Magento\DownloadableImportExport\Model\Import\Product\Type\Downloadable;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var HelperData
     */
    private $helper;

    /**
     * Setup environment for test
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->helper = $objectManagerHelper->getObject(HelperData::class);
    }

    /**
     * Test isRowDownloadableEmptyOptions with dataProvider
     *
     * @param array $rowData
     * @param bool $expected
     * @dataProvider isRowDownloadableEmptyOptionsDataProvider
     */
    public function testIsRowDownloadableEmptyOptions($rowData, $expected)
    {
        $this->assertEquals($expected, $this->helper->isRowDownloadableEmptyOptions($rowData));
    }

    /**
     * Data Provider to test isRowDownloadableEmptyOptions
     *
     * @return array
     */
    public static function isRowDownloadableEmptyOptionsDataProvider()
    {
        return [
            'Data set include downloadable link and sample' => [
                [
                    Downloadable::COL_DOWNLOADABLE_LINKS => 'https://magento2.com/download_link',
                    Downloadable::COL_DOWNLOADABLE_SAMPLES => 'https://magento2.com/sample_link'
                ],
                false
            ],
            'Data set with empty' => [
                [
                    Downloadable::COL_DOWNLOADABLE_LINKS => '',
                    Downloadable::COL_DOWNLOADABLE_SAMPLES => ''
                ],
                true
            ]
        ];
    }

    /**
     * Test isRowDownloadableNoValid with dataProvider
     *
     * @param array $rowData
     * @param bool $expected
     * @dataProvider isRowDownloadableNoValidDataProvider
     */
    public function isRowDownloadableNoValid($rowData, $expected)
    {
        $this->assertEquals($expected, $this->helper->isRowDownloadableNoValid($rowData));
    }

    /**
     * Data Provider to test isRowDownloadableEmptyOptions
     *
     * @return array
     */
    public function isRowDownloadableNoValidDataProvider()
    {
        return [
            'Data set include downloadable link and sample' => [
                [
                    Downloadable::COL_DOWNLOADABLE_LINKS => 'https://magento2.com/download_link',
                    Downloadable::COL_DOWNLOADABLE_SAMPLES => 'https://magento2.com/sample_link'
                ],
                true
            ],
            'Data set with empty' => [
                [
                    Downloadable::COL_DOWNLOADABLE_LINKS => '',
                    Downloadable::COL_DOWNLOADABLE_SAMPLES => ''
                ],
                false
            ]
        ];
    }

    /**
     * Test fillExistOptions with dataProvider
     *
     * @param array $base
     * @param array $option
     * @param array $existingOptions
     * @param array $expected
     * @dataProvider fillExistOptionsDataProvider
     */
    public function testFillExistOptions($base, $option, $existingOptions, $expected)
    {
        $this->assertEquals($expected, $this->helper->fillExistOptions($base, $option, $existingOptions));
    }

    /**
     * Data Provider to test fillExistOptions
     *
     * @return array
     */
    public static function fillExistOptionsDataProvider()
    {
        return [
            'Data set 1' => [
                [],
                [
                    'product_id' => 1,
                    'sample_type' => 'sample_type1',
                    'sample_url' => 'sample_url1',
                    'sample_file' => 'sample_file1',
                    'link_file' => 'link_file1',
                    'link_type' => 'link_type1',
                    'link_url' => 'link_url1'
                ],
                [
                    [
                        'product_id' => 1,
                        'sample_type' => 'sample_type1',
                        'sample_url' => 'sample_url1',
                        'sample_file' => 'sample_file1',
                        'link_file' => 'link_file1',
                        'link_type' => 'link_type1',
                        'link_url' => 'link_url1'
                    ],
                    [
                        'product_id' => 2,
                        'sample_type' => 'sample_type2',
                        'sample_url' => 'sample_url2',
                        'sample_file' => 'sample_file2',
                        'link_file' => 'link_file2',
                        'link_type' => 'link_type2',
                        'link_url' => 'link_url2'
                    ]
                ],
                [
                    'product_id' => 1,
                    'sample_type' => 'sample_type1',
                    'sample_url' => 'sample_url1',
                    'sample_file' => 'sample_file1',
                    'link_file' => 'link_file1',
                    'link_type' => 'link_type1',
                    'link_url' => 'link_url1'
                ]
            ],
            'Data set 2' => [
                [],
                [
                    'product_id' => 1,
                    'sample_type' => 'sample_type1',
                    'sample_url' => 'sample_url1',
                    'sample_file' => 'sample_file1',
                    'link_file' => 'link_file1',
                    'link_type' => 'link_type1',
                    'link_url' => 'link_url1'
                ],
                [],
                []
            ]
        ];
    }

    /**
     * Test prepareDataForSave with dataProvider
     *
     * @param array $base
     * @param array $replacement
     * @param array $expected
     * @dataProvider prepareDataForSaveDataProvider
     */
    public function testPrepareDataForSave($base, $replacement, $expected)
    {
        $this->assertEquals($expected, $this->helper->prepareDataForSave($base, $replacement));
    }

    /**
     * Data Provider to test prepareDataForSave
     *
     * @return array
     */
    public static function prepareDataForSaveDataProvider()
    {
        return [
            'Data set 1' => [
                [],
                [],
                []
            ],

            'Data set 2' => [
                [
                    'product_id' => 1,
                    'sample_type' => 'sample_type1',
                    'sample_url' => 'sample_url1',
                    'sample_file' => 'sample_file1',
                    'link_file' => 'link_file1',
                    'link_type' => 'link_type1',
                    'link_url' => 'link_url1'
                ],
                [
                    [
                        'product_id' => 2,
                        'sample_type' => 'sample_type2',
                        'sample_url' => 'sample_url2',
                        'sample_file' => 'sample_file2',
                        'link_file' => 'link_file2',
                        'link_type' => 'link_type2',
                        'link_url' => 'link_url2'
                    ]
                ],
                [
                    [
                        'product_id' => 2,
                        'sample_type' => 'sample_type2',
                        'sample_url' => 'sample_url2',
                        'sample_file' => 'sample_file2',
                        'link_file' => 'link_file2',
                        'link_type' => 'link_type2',
                        'link_url' => 'link_url2'
                    ]
                ]
            ]
        ];
    }

    /**
     * Test getTypeByValue with dataProvider
     *
     * @param string $option
     * @param string $expected
     * @dataProvider getTypeByValueDataProvider
     */
    public function testGetTypeByValue($option, $expected)
    {
        $this->assertEquals($expected, $this->helper->getTypeByValue($option));
    }

    /**
     * Data Provider for getTypeByValue
     *
     * @return array
     */
    public static function getTypeByValueDataProvider()
    {
        return [
            'Case File Option Value' => [
                'file1',
                Downloadable::FILE_OPTION_VALUE
            ],
            'Case url Option Value' => [
                'https://example.com',
                Downloadable::URL_OPTION_VALUE
            ]
        ];
    }
}
