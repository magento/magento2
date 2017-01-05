<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\Index;

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
