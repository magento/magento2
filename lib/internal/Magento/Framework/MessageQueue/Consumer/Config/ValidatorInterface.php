<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config;

/**
 * Queue consumer config validator interface.
 * @api
 */
interface ValidatorInterface
{
    /**
     * Validate merged consumer config data.
     *
     * @param array $configData
     * @return void
     * @throws \LogicException
     */
    public function validate($configData);
}
