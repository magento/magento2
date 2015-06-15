<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Resource;

interface SourceProviderInterface
{
    /**
     * Returns main table name - extracted from "module/table" style and
     * validated by db adapter
     *
     * @return string
     * @api
     */
    public function getMainTable();
}
