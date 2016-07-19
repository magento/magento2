<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config\Validator;

use \Magento\Framework\MessageQueue\Publisher\Config\ValidatorInterface;

/**
 * Publisher config data validator. Validates that publisher has only one enabled connection at the same time
 */
class ActiveConnection implements ValidatorInterface
{
    /**
     * Validate merged publisher config data.
     *
     * @param array $configData
     * @throws \LogicException
     */
    public function validate($configData)
    {

    }
}
