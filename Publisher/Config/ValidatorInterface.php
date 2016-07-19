<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config;

/**
 * Publisher config data validator.
 */
interface ValidatorInterface
{
    /**
     * Validate publisher config data.
     *
     * @param array $configData
     * @throws \LogicException
     */
    public function validate($configData);
}
