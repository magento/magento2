<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Listing;

/**
 * Interface RowInterface
 * @api
 * @since 100.0.2
 */
interface RowInterface
{
    /**
     * Get data
     *
     * @param array $dataRow
     * @param array $data
     * @return mixed
     */
    public function getData(array $dataRow, array $data = []);
}
