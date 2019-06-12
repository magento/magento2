<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\Index;

/**
 * @api
 * @since 100.1.0
 */
interface BuilderInterface
{
    /**
     * @return array
     * @since 100.1.0
     */
    public function build();

    /**
     * @param int $storeId
     * @return void
     * @since 100.1.0
     */
    public function setStoreId($storeId);
}
