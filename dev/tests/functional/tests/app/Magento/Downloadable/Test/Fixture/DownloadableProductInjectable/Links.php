<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Fixture\DownloadableProductInjectable;

use Mtf\Fixture\FixtureInterface;

/**
 * Class Links
 *
 * Preset for link block
 *
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class Links implements FixtureInterface
{
    /**
     * Prepared dataSet data
     *
     * @var array
     */
    protected $data;

    /**
     * Data set configuration settings
     *
     * @var array
     */
    protected $params;

    /**
     * @constructor
     * @param array $params
     * @param array $data
     */
    public function __construct(array $params, array $data = [])
    {
        $this->params = $params;
        $this->data = isset($data['preset']) ? $this->getPreset($data['preset']) : $data;
    }

    /**
     * Persist group price
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data set
     *
     * @param string $key [optional]
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
    }

    /**
     * Return data set configuration settings
     *
     * @return string
     */
    public function getDataConfig()
    {
        return $this->params;
    }

    /**
     * Preset array for downloadable links
     *
     * @param string $name
     * @return array|null
     */
    protected function getPreset($name)
    {
        $presets = [
            'default' => [
                'title' => 'Links%isolation%',
                'links_purchased_separately' => 'Yes',
                'downloadable' => [
                    'link' => [
                        [
                            'title' => 'link1%isolation%',
                            'price' => 2.43,
                            'number_of_downloads' => 2,
                            'sample' => [
                                'sample_type_url' => 'Yes',
                                'sample_url' => 'http://example.com',
                            ],
                            'file_type_url' => 'Yes',
                            'file_link_url' => 'http://example.com',
                            'is_shareable' => 'No',
                            'sort_order' => 1,
                        ],
                        [
                            'title' => 'link2%isolation%',
                            'price' => 3,
                            'number_of_downloads' => 3,
                            'sample' => [
                                'sample_type_url' => 'Yes',
                                'sample_url' => 'http://example.net',
                            ],
                            'file_type_url' => 'Yes',
                            'file_link_url' => 'http://example.net',
                            'is_shareable' => 'Yes',
                            'sort_order' => 0
                        ],
                    ],
                ],
            ],
            'with_two_separately_links' => [
                'title' => 'Links%isolation%',
                'links_purchased_separately' => 'Yes',
                'downloadable' => [
                    'link' => [
                        [
                            'title' => 'link1%isolation%',
                            'price' => 2.43,
                            'number_of_downloads' => 2,
                            'is_shareable' => 'No',
                            'sample' => [
                                'sample_type_url' => 'Yes',
                                'sample_url' => 'http://example.com/sample',
                            ],
                            'file_type_url' => 'Yes',
                            'file_link_url' => 'http://example.com',
                            'sort_order' => 0,
                        ],
                        [
                            'title' => 'link2%isolation%',
                            'price' => 3,
                            'number_of_downloads' => 3,
                            'is_shareable' => 'Yes',
                            'sample' => [
                                'sample_type_url' => 'Yes',
                                'sample_url' => 'http://example.net/sample2',
                            ],
                            'file_type_url' => 'Yes',
                            'file_link_url' => 'http://example.net/',
                            'sort_order' => 1
                        ],
                    ],
                ],
            ],
            'with_three_links' => [
                'title' => 'Links%isolation%',
                'links_purchased_separately' => 'Yes',
                'downloadable' => [
                    'link' => [
                        [
                            'title' => 'link1%isolation%',
                            'price' => 2.43,
                            'number_of_downloads' => 2,
                            'sample' => [
                                'sample_type_url' => 'Yes',
                                'sample_url' => 'http://example.com',
                            ],
                            'file_type_url' => 'Yes',
                            'file_link_url' => 'http://example.com',
                            'is_shareable' => 'No',
                            'sort_order' => 0,
                        ],
                        [
                            'title' => 'link2%isolation%',
                            'price' => 3,
                            'number_of_downloads' => 3,
                            'sample' => [
                                'sample_type_url' => 'Yes',
                                'sample_url' => 'http://example.net',
                            ],
                            'file_type_url' => 'Yes',
                            'file_link_url' => 'http://example.net',
                            'is_shareable' => 'Yes',
                            'sort_order' => 1
                        ],
                        [
                            'title' => 'link3%isolation%',
                            'price' => 5.43,
                            'number_of_downloads' => 5,
                            'sample' => [
                                'sample_type_url' => 'Yes',
                                'sample_url' => 'http://example.net',
                            ],
                            'file_type_url' => 'Yes',
                            'file_link_url' => 'http://example.net',
                            'is_shareable' => 'Yes',
                            'sort_order' => 2
                        ],
                    ],
                ],
            ],
        ];
        if (!isset($presets[$name])) {
            return null;
        }
        return $presets[$name];
    }
}
