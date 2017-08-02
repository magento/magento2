<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\ResourceModel\Config\Data;

/**
 * Config data collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @api
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Framework\App\Config\Value::class,
            \Magento\Config\Model\ResourceModel\Config\Data::class
        );
    }

    /**
     * Add scope filter to collection
     *
     * @param string $scope
     * @param int $scopeId
     * @param string $section
     * @return $this
     * @since 2.0.0
     */
    public function addScopeFilter($scope, $scopeId, $section)
    {
        $this->addFieldToFilter('scope', $scope);
        $this->addFieldToFilter('scope_id', $scopeId);
        $this->addFieldToFilter('path', ['like' => $section . '/%']);
        return $this;
    }

    /**
     *  Add path filter
     *
     * @param string $section
     * @return $this
     * @since 2.0.0
     */
    public function addPathFilter($section)
    {
        $this->addFieldToFilter('path', ['like' => $section . '/%']);
        return $this;
    }

    /**
     * Add value filter
     *
     * @param int|string $value
     * @return $this
     * @since 2.0.0
     */
    public function addValueFilter($value)
    {
        $this->addFieldToFilter('value', ['like' => $value]);
        return $this;
    }
}
