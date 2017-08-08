<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config;

/**
 * Publisher config data validator.
 * @since 2.2.0
 */
interface ValidatorInterface
{
    /**
     * Validate publisher config data.
     *
     * @param array $configData
     * @throws \LogicException
     * @return void
     * @since 2.2.0
     */
    public function validate($configData);
}
