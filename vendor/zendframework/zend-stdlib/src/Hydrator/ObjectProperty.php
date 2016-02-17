<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Stdlib\Hydrator;

use Zend\Stdlib\Exception;
use ReflectionClass;
use ReflectionProperty;

class ObjectProperty extends AbstractHydrator
{
    /**
     * @var array[] indexed by class name and then property name
     */
    private static $skippedPropertiesCache = array();

    /**
     * {@inheritDoc}
     *
     * Extracts the accessible non-static properties of the given $object.
     *
     * @throws Exception\BadMethodCallException for a non-object $object
     */
    public function extract($object)
    {
        if (!is_object($object)) {
            throw new Exception\BadMethodCallException(
                sprintf('%s expects the provided $object to be a PHP object)', __METHOD__)
            );
        }

        $data   = get_object_vars($object);
        $filter = $this->getFilter();

        foreach ($data as $name => $value) {
            // Filter keys, removing any we don't want
            if (! $filter->filter($name)) {
                unset($data[$name]);
                continue;
            }

            // Replace name if extracted differ
            $extracted = $this->extractName($name, $object);

            if ($extracted !== $name) {
                unset($data[$name]);
                $name = $extracted;
            }

            $data[$name] = $this->extractValue($name, $value, $object);
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     *
     * Hydrate an object by populating public properties
     *
     * Hydrates an object by setting public properties of the object.
     *
     * @throws Exception\BadMethodCallException for a non-object $object
     */
    public function hydrate(array $data, $object)
    {
        if (!is_object($object)) {
            throw new Exception\BadMethodCallException(
                sprintf('%s expects the provided $object to be a PHP object)', __METHOD__)
            );
        }

        $properties = & self::$skippedPropertiesCache[get_class($object)];

        if (! isset($properties)) {
            $reflection = new ReflectionClass($object);
            $properties = array_fill_keys(
                array_map(
                    function (ReflectionProperty $property) {
                        return $property->getName();
                    },
                    $reflection->getProperties(
                        ReflectionProperty::IS_PRIVATE
                        + ReflectionProperty::IS_PROTECTED
                        + ReflectionProperty::IS_STATIC
                    )
                ),
                true
            );
        }

        foreach ($data as $name => $value) {
            $property = $this->hydrateName($name, $data);

            if (isset($properties[$property])) {
                continue;
            }

            $object->$property = $this->hydrateValue($property, $value, $data);
        }

        return $object;
    }
}
