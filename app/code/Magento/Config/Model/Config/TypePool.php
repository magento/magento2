<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config;

/**
 * Checks whether the field is of type.
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
     * List of sensitive configuration fields.
     *
     * @var array
     */
    private $sensitive;

    /**
     * List of environment configuration fields.
     *
     * @var array
     */
    private $environment;

    /**
     * @param array $sensitive list of sensitive configuration fields
     * @param array $environment list of environment configuration fields
     */
    public function __construct(array $sensitive = [], array $environment = [])
    {
        $this->sensitive = $sensitive;
        $this->environment = $environment;
    }

    /**
     * Verifies that the configuration field belongs to the specified type.
     *
     * @param string $path configuration field. For example 'contact/email/recipient_email'
     * @param string $type type of configuration fields
     * @return bool
     */
    public function isPresent($path, $type)
    {
        $paths = $this->getPathsByType($type);
        return in_array($path, $paths);
    }

    /**
     * Gets a list of configuration fields for the specified type.
     *
     * @param string $type type configuration fields. For example 'contact/email/recipient_email'
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
