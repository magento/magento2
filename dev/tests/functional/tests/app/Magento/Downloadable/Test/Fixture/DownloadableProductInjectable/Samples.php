<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Fixture\DownloadableProductInjectable;

use Mtf\Fixture\FixtureInterface;

/**
 * Class Samples
 * Preset for sample block
 */
class Samples implements FixtureInterface
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
     * Construct for class
     *
     * @param array $params
     * @param array $data
     */
    public function __construct(array $params, array $data = [])
    {
        $this->params = $params;
        $this->data = isset($data['preset']) ? $this->getPreset($data['preset']) : $data;
    }

    /**
     * Persist fixture
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
     * Preset array for downloadable samples
     *
     * @param string $name
     * @return array|null
     */
    protected function getPreset($name)
    {
        $presets = [
            'default' => [
                'title' => 'Samples%isolation%',
                'downloadable' => [
                    'sample' => [
                        [
                            'title' => 'sample1%isolation%',
                            'sample_type_url' => 'Yes',
                            'sample_url' => 'http://example.com',
                            'sort_order' => 0,
                        ],
                        [
                            'title' => 'sample2%isolation%',
                            'sample_type_url' => 'Yes',
                            'sample_url' => 'http://example2.com',
                            'sort_order' => 1
                        ],
                    ],
                ],
            ],
            'with_three_samples' => [
                'title' => 'Samples%isolation%',
                'downloadable' => [
                    'sample' => [
                        [
                            'title' => 'sample1%isolation%',
                            'sample_type_url' => 'Yes',
                            'sample_url' => 'http://example.com',
                            'sort_order' => 0,
                        ],
                        [
                            'title' => 'sample2%isolation%',
                            'sample_type_url' => 'Yes',
                            'sample_url' => 'http://example2.com',
                            'sort_order' => 1
                        ],
                        [
                            'title' => 'sample3%isolation%',
                            'sample_type_url' => 'Yes',
                            'sample_url' => 'http://example3.com',
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
