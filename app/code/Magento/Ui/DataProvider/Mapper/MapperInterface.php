<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider\Mapper;

/**
 * Interface MapperInterface
 * @since 2.1.0
 */
interface MapperInterface
{
    /**
     * Retrieve mapped values
     *
     * @return array
     * @since 2.1.0
     */
    public function getMappings();
}
