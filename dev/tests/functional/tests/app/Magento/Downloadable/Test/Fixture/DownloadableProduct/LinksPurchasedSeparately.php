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
namespace Magento\Downloadable\Test\Fixture\DownloadableProduct;

use Mtf\Factory\Factory;
use Magento\Downloadable\Test\Fixture\DownloadableProduct;

class LinksPurchasedSeparately extends DownloadableProduct
{
    /**
     * {inheritdoc}
     */
    protected function _initData()
    {
        parent::_initData();
        $this->_data = array_replace_recursive(
            $this->_data,
            [
                'fields' => [
                    'downloadable_link_purchase_type' => [
                        'value' => 'Yes',
                        'input_value' => '1',
                    ],
                    'downloadable' => [
                        'link' => [
                            [
                                'title' => ['value' => 'row1'],
                                'price' => ['value' => 2],
                                'number_of_downloads' => ['value' => 2],
                                'sample][type' => ['value' => 'url', 'input' => 'radio'],
                                'sample][url' => ['value' => 'http://example.com'],
                                'type' => ['value' => 'url', 'input' => 'radio'],
                                'link_url' => ['value' => 'http://example.com'],
                                'is_shareable' => [ // needed explicit default value for CURL handler
                                    'input' => 'select',
                                    'input_value' => static::LINK_IS_SHAREABLE_USE_CONFIG_VALUE,
                                ],
                            ]
                        ],
                        'group' => static::GROUP
                    ]
                ]
            ]
        );

        $this->_repository = Factory::getRepositoryFactory()
            ->getMagentoDownloadableDownloadableProduct($this->_dataConfig, $this->_data);
    }
}
