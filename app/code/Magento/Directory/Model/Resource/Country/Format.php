<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Resource\Country;

/**
 * \Directory country format resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Format extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('directory_country_format', 'country_format_id');
    }

    /**
     * Initialize unique fields
     *
     * @return \Magento\Directory\Model\Resource\Country\Format
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = [
            [
                'field' => ['country_id', 'type'],
                'title' => __('Country and Format Type combination should be unique'),
            ],
        ];
        return $this;
    }
}
