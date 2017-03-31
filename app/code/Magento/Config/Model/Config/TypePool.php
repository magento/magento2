<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config;

/**
 * Checker for config type.
 *
 * Used when you need to know if the configuration path belongs to a certain type.
 * Participates in the mechanism for creating the configuration dump file.
 */
class TypePool
{
    /**
     * Sensitive type.
     */
    const TYPE_SENSITIVE = 'sensitive';

    /**
     * Environment type.
     */
    const TYPE_ENVIRONMENT = 'environment';

    /**
     * List of sensitive configuration fields paths.
     *
     * @var array
     */
    private $sensitive;

    /**
     * List of environment configuration fields paths.
     *
     * @var array
     */
    private $environment;

    /**
     * Filtered configuration array
     *
     * @var array
     */
    private $filteredPaths;

    /**
     * @param array $sensitive List of sensitive configuration fields paths
     * @param array $environment List of environment configuration fields paths
     */
    public function __construct(array $sensitive = [], array $environment = [])
    {
        $this->sensitive = $sensitive;
        $this->environment = $environment;
    }

    /**
     * Verifies that the configuration field path belongs to the specified type.
     *
     * @param string $path Configuration field path. For example, 'contact/email/recipient_email'
     * @param string $type Type of configuration fields
     * @return bool True when the path belongs to requested type, false otherwise
     */
    public function isPresent($path, $type)
    {
        if (!isset($this->filteredPaths[$type])) {
            $this->filteredPaths[$type] = $this->getPathsByType($type);
        }

        return in_array($path, $this->filteredPaths[$type]);
    }

    /**
     * Gets a list of configuration fields paths for the specified type.
     *
     * Returns an empty array if the passed type does not exist. If the type exists,
     * it returns a list of fields of a persistent type.
     * For example, if you pass a sensitive or TypePool::TYPE_SENSITIVE type, we get an array:
     * ```php
     * array(
     *      'some/path/sensetive/path1'
     *      'some/path/sensetive/path2'
     *      'some/path/sensetive/path3'
     *      'some/path/sensetive/path4'
     * );
     * ```
     *
     * @param string $type Type of configuration fields. Allowed values of types:
     * - sensitive or TypePool::TYPE_SENSITIVE;
     * - environment or TypePool::TYPE_ENVIRONMENT.
     * @return array
     */
    private function getPathsByType($type)
    {
        switch ($type) {
            case self::TYPE_SENSITIVE:
                $paths = $this->sensitive;
                break;
            case self::TYPE_ENVIRONMENT:
                $paths = $this->environment;
                break;
            default:
                return [];
        }

        return array_keys(array_filter(
            $paths,
            function ($value) {
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }
        ));
    }
}
