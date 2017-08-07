<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\DeploymentConfig;

/**
 * Validator interface for section data from shared configuration files.
 * @since 2.2.0
 */
interface ValidatorInterface
{
    /**
     * Validates data and returns messages with causes of wrong data.
     * Returns empty array if data is valid
     *
     * @param array $data Data that should be validated
     * @return string[] The array of messages with description of wrong data.
     * @since 2.2.0
     */
    public function validate(array $data);
}
