<?php

namespace Magento\Store\Api;

interface StoreCreateManagementInterface
{
    /**
     *
     * @param  array $data
     * @return \Magento\Store\Model\Store
     */
    public function create($data);

    /**
     *
     * @return int
     */
    public function getDefaultGroupId();
}
