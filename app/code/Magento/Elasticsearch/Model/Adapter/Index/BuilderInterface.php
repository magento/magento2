<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
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
