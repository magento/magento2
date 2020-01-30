<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Model\Config;

use Magento\Framework\Exception\ValidatorException;

/**
 * Validates the config path by config structure schema.
 * @api
 * @since 101.0.0
 */
class PathValidator
{
    /**
     * The config structure.
     *
     * @var Structure
     */
    private $structure;

    /**
     * @param Structure $structure The config structure
     */
    public function __construct(Structure $structure)
    {
        $this->structure = $structure;
    }

    /**
     * Checks whether the config path present in configuration structure.
     *
     * @param string $path The config path
     * @return true The result of validation
     * @throws ValidatorException If provided path is not valid
     * @since 101.0.0
     */
    public function validate($path)
    {
        $allPaths = $this->structure->getFieldPaths();

        if (!array_key_exists($path, $allPaths)) {
            throw new ValidatorException(__('The "%1" path doesn\'t exist. Verify and try again.', $path));
        }

        return true;
    }
}
