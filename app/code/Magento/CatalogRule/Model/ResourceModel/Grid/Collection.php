<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\ResourceModel\Grid;

/**
 * Class \Magento\CatalogRule\Model\ResourceModel\Grid\Collection
 *
 * @since 2.0.0
 */
class Collection extends \Magento\CatalogRule\Model\ResourceModel\Rule\Collection
{
    /**
     * @return $this
     * @since 2.0.0
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addWebsitesToResult();

        return $this;
    }
}
