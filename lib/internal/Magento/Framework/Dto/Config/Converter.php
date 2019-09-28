<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Dto\Config;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use InvalidArgumentException;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Config\ConverterInterface;

/**
 * Configuration converter class for DTO definition
 */
class Converter implements ConverterInterface
{
    /**
     * Convert dom node tree to array
     *
     * @param DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $output = [
            'interface' => [],
            'class' => [],
        ];
        if (!$source instanceof DOMDocument) {
            return $output;
        }

        /** @var DOMNodeList $interfaceNodes */
        $interfaceNodes = $source->getElementsByTagName('interface');

        /** @var DOMNodeList $classNodes */
        $classNodes = $source->getElementsByTagName('class');

        /** @var DOMNode $interfaceNode */
        foreach ($interfaceNodes as $interfaceNode) {
            $interfaceName = (string) $interfaceNode->getAttribute('name');
            $isMutable = filter_var($interfaceNode->getAttribute('mutable'), FILTER_VALIDATE_BOOLEAN);

            if (isset($output[$interfaceName])) {
                throw new InvalidArgumentException('Multiple definitions of ' . $interfaceName . ' exist');
            }

            $output['interface'][$interfaceName] = [
                'type' => 'interface',
                'mutable' => $isMutable,
                'properties' => []
            ];

            $properties = $interfaceNode->getElementsByTagName('property');
            foreach ($properties as $property) {
                $propertyId = SimpleDataObjectConverter::snakeCaseToCamelCase($property->getAttribute('name'));
                $propertyType = $property->getAttribute('type');
                $propertyOptional = filter_var($property->getAttribute('optional'), FILTER_VALIDATE_BOOLEAN);
                $propertyNullable = filter_var($property->getAttribute('nullable'), FILTER_VALIDATE_BOOLEAN);

                $output['interface'][$interfaceName]['properties'][$propertyId] = [
                    'type' => $propertyType,
                    'optional' => $propertyOptional,
                    'nullable' => $propertyNullable
                ];
            }
        }

        foreach ($classNodes as $classNode) {
            $className = (string) $classNode->getAttribute('name');
            $forInterface = (string) $classNode->getAttribute('for');

            if (isset($output['class'][$className])) {
                throw new InvalidArgumentException('Multiple definitions of ' . $className . ' exist');
            }

            if (!isset($output['interface'][$forInterface])) {
                throw new InvalidArgumentException(
                    'DTO interface definition for ' . $forInterface . ' is missing'
                );
            }

            $output['class'][$className] = $forInterface;
        }

        return $output;
    }
}
