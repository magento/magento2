<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider\Mapper;

/**
 * Interface MapperInterface
 *
 * @api
 */
interface MapperInterface
{
    /**
     * Retrieve mapped values
     *
     * @return array
     */
    public function getMappings();
}
