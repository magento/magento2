<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config;

use Magento\Config\Model\Config\Export\ExcludeList;

/**
 * Checker for config type.
 *
 * Used when you need to know if the configuration path belongs to a certain type.
 * Participates in the mechanism for creating the configuration dump file.
 * @api
 * @since 100.2.0
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
     * Checks if the configuration path is contained in exclude list.
     *
     * @var ExcludeList
     * @deprecated 100.2.0 We use it only to support backward compatibility. If some configurations
     *             were set to this list before, we need to read them.
     *             It will be supported for next 2 minor releases or until a major release.
     *             TypePool should be used to mark configurations with types.
     * @see TypePool
     */
    private $excludeList;

    /**
     * @param array $sensitive List of sensitive configuration fields paths
     * @param array $environment List of environment configuration fields paths
     * @param ExcludeList $excludeList Checks if the configuration path is contained in exclude list
     */
    public function __construct(array $sensitive = [], array $environment = [], ExcludeList $excludeList = null)
    {
        $this->sensitive = $sensitive;
        $this->environment = $environment;
        $this->excludeList = $excludeList;
    }

    /**
     * Verifies that the configuration field path belongs to the specified type.
     *
     * For sensitive type, if configuration path was not found in the sensitive type pool
     * checks if this configuration path present in ExcludeList. It used only to support backward compatibility.
     * If some configurations were set to ExcludeList before, we need to read them.
     * It will be supported for next 2 minor releases or until a major release.
     *
     * @param string $path Configuration field path. For example, 'contact/email/recipient_email'
     * @param string $type Type of configuration fields
     * @return bool True when the path belongs to requested type, false otherwise
     * @since 100.2.0
     */
    public function isPresent($path, $type)
    {
        if (!isset($this->filteredPaths[$type])) {
            $this->filteredPaths[$type] = $this->getPathsByType($type);
        }

        $isPresent = in_array($path, $this->filteredPaths[$type]);

        if ($type == self::TYPE_SENSITIVE
            && !$isPresent
            && $this->excludeList instanceof ExcludeList
        ) {
            $isPresent = $this->excludeList->isPresent($path);
        }

        return $isPresent;
    }

    /**
     * Gets a list of configuration fields paths for the specified type.
     *
     * Returns an empty array if the passed type does not exist. If the type exists,
     * it returns a list of fields of a persistent type.
     * For example, if you pass a sensitive or TypePool::TYPE_SENSITIVE type, we get an array:
     * ```php
     * array(
     *      'some/path/sensitive/path1',
     *      'some/path/sensitive/path2'
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
