<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\Product;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Image entity data source.
 */
class Image extends DataSource
{
    /**
     * Image constructor.
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, $data = [])
    {
        foreach ($data as $key => &$imageData) {
            if (isset($imageData['file']) && file_exists(MTF_TESTS_PATH . $imageData['file'])) {
                $imageData['file'] = MTF_TESTS_PATH . $imageData['file'];
            } else {
                unset($data[$key]);
            }
        }
        $this->data = $data;
    }
}
