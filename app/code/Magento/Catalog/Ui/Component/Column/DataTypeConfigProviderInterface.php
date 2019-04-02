<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\Component\Column;

/**
 * Interface DataTypeConfigProviderInterface
 *
 * @package Magento\Catalog\Ui\Component\Column
 */
interface DataTypeConfigProviderInterface
{
    /**
     * @param string $dataType
     *
     * @return array
     */
    public function getConfig(string $dataType):array;
}
