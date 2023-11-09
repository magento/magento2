<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 */

namespace Magento\Sitemap\Model\ResourceModel\Catalog;

use Magento\Framework\DB\Select;

class SelectWrapper
{
    /**
     * Allow to modify select statement with plugins
     *
     * @param Select $select
     * @return Select
     */
    public function getSelectStatement(Select $select)
    {
        return $select;
    }
}
