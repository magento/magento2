<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Config\Model\Config;

use Magento\Config\Model\Config\Structure\Element\Field;
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
     * Allows partial path validation: if any config path starts with the given path, it's valid.
     *
     * @param string $path The config path (can be partial)
     * @return true The result of validation
     * @throws ValidatorException If provided path is not valid
     * @since 101.0.0
     */
    public function validate($path)
    {
        $element = $this->structure->getElementByConfigPath($path);
        if ($element instanceof Field && $element->getConfigPath()) {
            $path = $element->getConfigPath();
        }

        $allPaths = $this->structure->getFieldPaths();

        // Allow exact or partial path match
        $found = false;
        foreach (array_keys($allPaths) as $fullPath) {
            if ($fullPath === $path || str_starts_with($fullPath, $path . '/')) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new ValidatorException(__('The "%1" path doesn\'t exist. Verify and try again.', $path));
        }

        return true;
    }
}
