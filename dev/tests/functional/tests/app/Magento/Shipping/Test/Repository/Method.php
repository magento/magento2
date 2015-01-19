<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class Method Repository
 * Shipping methods
 *
 */
class Method extends AbstractRepository
{
    /**
     * {inheritdoc}
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'config' => $defaultConfig,
            'data' => $defaultData,
        ];

        $this->_data['free_shipping'] = $this->_getFreeShipping();
        $this->_data['flat_rate'] = $this->_getFlatRate();
        // Shipping carriers
        $this->_data['dhl_eu'] = $this->_getDhlEU();
        $this->_data['dhl_uk'] = $this->_getDhlUK();
        $this->_data['fedex'] = $this->_getFedex();
        $this->_data['ups'] = $this->_getUps();
        $this->_data['usps'] = $this->_getUsps();
    }

    protected function _getFreeShipping()
    {
        return [
            'data' => [
                'fields' => [
                    'shipping_service' => 'Free Shipping',
                    'shipping_method' => 'Free',
                ],
            ]
        ];
    }

    protected function _getFlatRate()
    {
        return [
            'data' => [
                'fields' => [
                    'shipping_service' => 'Flat Rate',
                    'shipping_method' => 'Fixed',
                ],
            ]
        ];
    }

    protected function _getDhlEU()
    {
        return [
            'data' => [
                'fields' => [
                    'shipping_service' => 'DHL',
                    'shipping_method' => 'Express worldwide',
                ],
            ]
        ];
    }

    protected function _getDhlUK()
    {
        return [
            'data' => [
                'fields' => [
                    'shipping_service' => 'DHL',
                    'shipping_method' => 'Domestic express',
                ],
            ]
        ];
    }

    protected function _getFedex()
    {
        return [
            'data' => [
                'fields' => [
                    'shipping_service' => 'Federal Express',
                    'shipping_method' => 'Ground',
                ],
            ]
        ];
    }

    protected function _getUps()
    {
        return [
            'data' => [
                'fields' => [
                    'shipping_service' => 'United Parcel Service',
                    'shipping_method' => 'Ground',
                ],
            ]
        ];
    }

    protected function _getUsps()
    {
        return [
            'data' => [
                'fields' => [
                    'shipping_service' => 'United States Postal Service',
                    'shipping_method' => 'Mail',  /** @todo change to 'Priority Mail' when usps config is updated */
                ],
            ]
        ];
    }
}
