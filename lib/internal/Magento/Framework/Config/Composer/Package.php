<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    public function __construct(\StdClass $json)
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
