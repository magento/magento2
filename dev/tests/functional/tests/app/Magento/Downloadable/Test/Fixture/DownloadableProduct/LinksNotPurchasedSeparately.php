<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Fixture\DownloadableProduct;

use Mtf\Factory\Factory;

/**
 * Class LinksNotPurchasedSeparately
 * Init downloadable data not purchased separately
 */
class LinksNotPurchasedSeparately extends LinksPurchasedSeparately
{
    /**
     * Init downloadable data
     */
    protected function _initData()
    {
        parent::_initData();

        $this->_data = array_replace_recursive(
            $this->_data,
            [
                'fields' => [
                    'downloadable_link_purchase_type' => [
                        'value' => 'No',
                        'input_value' => '0',
                    ],
                ]
            ]
        );

        $this->_repository = Factory::getRepositoryFactory()
            ->getMagentoDownloadableDownloadableProduct($this->_dataConfig, $this->_data);
    }
}
