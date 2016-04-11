<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Config\Composer;

/**
 * A model that represents composer package
 */
class Package
{
    /**
     * Contents of composer.json
     *
     * @var \StdClass
     */
    protected $json;

    /**
     * Constructor
     *
     * @param \StdClass $json
     */
    public function __construct(\stdClass $json)
    {
        $this->json = $json;
    }

    /**
     * Get JSON contents
     *
     * @param bool $formatted
     * @param string|null $format
     * @return string|\StdClass
     */
    public function getJson($formatted = true, $format = null)
    {
        if ($formatted) {
            if (null === $format) {
                $format = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES;
            }
            return json_encode($this->json, $format) . "\n";
        }
        return $this->json;
    }

    /**
     * A getter for properties of the package
     *
     * For example:
     *     $package->get('name');
     *     $package->get('version');
     *     $package->get('require->php');
     *
     * Returns whatever there is in the node or false if was unable to find this node
     *
     * @param string $propertyPath
     * @param string $filter pattern to filter out the properties
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function get($propertyPath, $filter = null)
    {
        $result = $this->traverseGet($this->json, explode('->', $propertyPath));
        if ($result && $filter) {
            foreach ($result as $key => $value) {
                if (!preg_match($filter, $key)) {
                    unset($result->{$key});
                }
            }
        }
        return $result;
    }

    /**
     * Traverse an \StdClass object recursively in search of the needed property
     *
     * @param \StdClass $json
     * @param array $chain
     * @param int $index
     * @return mixed
     */
    private function traverseGet(\StdClass $json, array $chain, $index = 0)
    {
        $property = $chain[$index];
        if (!property_exists($json, $property)) {
            return false;
        }
        if (isset($chain[$index + 1])) {
            return $this->traverseGet($json->{$property}, $chain, $index + 1);
        } else {
            return $json->{$property};
        }
    }
}
