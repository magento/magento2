<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config;

/**
 * Queue consumer config validator interface.
 * @since 2.2.0
 */
interface ValidatorInterface
{
    /**
     * Validate merged consumer config data.
     *
     * @param array $configData
     * @return void
     * @throws \LogicException
     * @since 2.2.0
     */
    public function validate($configData);
}
