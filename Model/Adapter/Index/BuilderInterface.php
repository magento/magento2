<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\Index;

/**
 * @api
 */
interface BuilderInterface
{
    /**
     * @return array
     */
    public function build();

    /**
     * @param int $storeId
     * @return void
     */
    public function setStoreId($storeId);
}
