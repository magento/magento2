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

namespace Magento\Downloadable\Test\Handler\CatalogProductDownloadable;

use Mtf\Fixture\FixtureInterface;
use Magento\Catalog\Test\Handler\CatalogProductSimple\Curl as AbstractCurl;

/**
 * Class Curl
 * Create new downloadable product via curl
 */
class Curl extends AbstractCurl implements CatalogProductDownloadableInterface
{
    /**
     * Post request for creating downloadable product
     *
     * @param FixtureInterface $fixture [optional]
     * @return array
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $this->extendPlaceholder();
        $config = $fixture->getDataConfig();
        $prefix = isset($config['input_prefix']) ? $config['input_prefix'] : null;
        $data = $this->prepareData($fixture, $prefix);

        if ($prefix) {
            $data['downloadable'] = $data[$prefix]['downloadable'];
            unset($data[$prefix]['downloadable']);
        }

        return ['id' => $this->createProduct($data, $config)];
    }

    /**
     * Expand basic placeholder
     *
     * @return void
     */
    protected function extendPlaceholder()
    {
        $this->mappingData += [
            'links_purchased_separately' => [
                'Yes' => 1,
                'No' => 0
            ],
            'is_shareable' => [
                'Yes' => 1,
                'No' => 0,
                'Use config' => 2
            ],
        ];
    }
}
