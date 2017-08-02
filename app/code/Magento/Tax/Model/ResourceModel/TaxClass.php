<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\ResourceModel;

/**
 * Tax class resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class TaxClass extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     * @since 2.0.0
     */
    public function _construct()
    {
        $this->_init('tax_class', 'class_id');
    }

    /**
     * Initialize unique fields
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = [
            [
                'field' => ['class_type', 'class_name'],
                'title' => __('Class name and class type'),
            ],
        ];
        return $this;
    }
}
