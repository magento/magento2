<?php

namespace Magento\AsyncConfig\Api\Data;

interface AsyncConfigMessageInterface
{
    /**
     * @return string
     */
    public function getConfigData();

    /**
     * @param string $data
     * @return void
     */
    public function setConfigData(string $data);
}
