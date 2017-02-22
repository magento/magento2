<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config;

use Magento\Framework\Exception\ValidatorException;

/**
 * Validates the config path by config structure schema.
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
     * Validates the config path by config structure schema.
     *
     * @param string $path The config path
     * @return bool The result of validation
     * @throws ValidatorException If provided path is not valid
     */
    public function validate($path)
    {
        $allPaths = $this->structure->getFieldPaths();

        if (!array_key_exists($path, $allPaths)) {
            throw new ValidatorException(__('The "%1" path does not exists', $path));
        }

        return true;
    }
}
