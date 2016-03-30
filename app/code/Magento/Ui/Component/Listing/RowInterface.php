<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Listing;

/**
 * Interface RowInterface
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
