<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Model\Config;

use Magento\Config\App\Config\Source\RuntimeConfigSource;
use Magento\Framework\Exception\ValidatorException;

/**
 * Validates the config path by config structure schema.
 * @api
 * @since 101.0.0
 */
class PathValidator
{
    /**
     * Source of configurations.
     *
     * @var RuntimeConfigSource
     */
    private $configSource;

    /**
     * @param RuntimeConfigSource $configSource Source of configurations
     */
    public function __construct(
        RuntimeConfigSource $configSource
    ) {
        $this->configSource = $configSource;
    }

    /**
     * Checks whether the config path present in configuration.
     *
     * @param string $path The config path
     * @return true The result of validation
     * @throws ValidatorException If provided path is not valid
     * @since 101.0.0
     */
    public function validate($path)
    {
        if (is_null($this->configSource->get($path))) {
            throw new ValidatorException(__('The "%1" path doesn\'t exist. Verify and try again.', $path));
        }

        return true;
    }
}
