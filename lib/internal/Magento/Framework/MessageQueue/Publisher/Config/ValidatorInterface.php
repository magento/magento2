<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config;

/**
 * Publisher config data validator.
 * @api
 */
interface ValidatorInterface
{
    /**
     * Validate publisher config data.
     *
     * @param array $configData
     * @throws \LogicException
     * @return void
     */
    public function validate($configData);
}
