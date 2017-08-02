<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Listing;

/**
 * Interface RowInterface
 * @api
 * @since 2.0.0
 */
interface RowInterface
{
    /**
     * Get data
     *
     * @param array $dataRow
     * @param array $data
     * @return mixed
     * @since 2.0.0
     */
    public function getData(array $dataRow, array $data = []);
}
