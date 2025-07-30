<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AsyncConfig\Api\Data;

interface AsyncConfigMessageInterface
{
    /**
     * Get Configuration data
     *
     * @return string
     */
    public function getConfigData();

    /**
     * Set Configuration data
     *
     * @param string $data
     * @return void
     */
    public function setConfigData(string $data);
}
