<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue\Topology\Config;

/**
 * Topology config data validator.
 * @api
 */
interface ValidatorInterface
{
    /**
     * Validate topology config data.
     *
     * @param array $configData
     * @throws \LogicException
     * @return void
     */
    public function validate($configData);
}
