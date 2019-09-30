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
     * Convert interface nodes
     *
     * @param DOMDocument $source
     * @return array
     */
    private function convertInterface(DOMDocument $source): array
    {
        $output = [];

        /** @var DOMNodeList $interfaceNodes */
        $interfaceNodes = $source->getElementsByTagName('interface');

        /** @var DOMNode $interfaceNode */
        foreach ($interfaceNodes as $interfaceNode) {
            $interfaceName = (string) $interfaceNode->getAttribute('type');
            $isMutable = filter_var($interfaceNode->getAttribute('mutable'), FILTER_VALIDATE_BOOLEAN);

            if (isset($output[$interfaceName])) {
                throw new InvalidArgumentException('Multiple definitions of ' . $interfaceName . ' exist');
            }

            $output[$interfaceName] = [
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

                $output[$interfaceName]['properties'][$propertyId] = [
                    'type' => $propertyType,
                    'optional' => $propertyOptional,
                    'nullable' => $propertyNullable
                ];
            }
        }

        return $output;
    }

    /**
     * Throw an exception if the requested interface is not declared
     *
     * @param array $interfaces
     * @param string $interfaceName
     */
    private function assertInterfaceExists(array $interfaces, string $interfaceName): void
    {
        if (!isset($interfaces[$interfaceName])) {
            throw new InvalidArgumentException(
                'DTO interface ' . $interfaceName . ' definition is missing'
            );
        }
    }

    /**
     * Convert classes
     *
     * @param array $interfaces
     * @param DOMDocument $source
     * @return array
     */
    private function convertClasses(array $interfaces, DOMDocument $source): array
    {
        $output = [];

        /** @var DOMNodeList $classNodes */
        $classNodes = $source->getElementsByTagName('class');

        foreach ($classNodes as $classNode) {
            $className = (string) $classNode->getAttribute('type');
            $forInterface = (string) $classNode->getAttribute('for');

            if (isset($output[$className])) {
                throw new InvalidArgumentException('Multiple definitions of ' . $className . ' exist');
            }

            $this->assertInterfaceExists($interfaces, $forInterface);

            $output[$className] = $forInterface;
        }

        return $output;
    }

    /**
     * Extract processor definition
     *
     * @param DOMNodeList $nodes
     * @return array
     */
    private function extractProcessor(DOMNodeList $nodes): array
    {
        $types = [];
        $sortOrders = [];

        foreach ($nodes as $node) {
            $types[] = (string) $node->getAttribute('type');
            $sortOrders[] = (int) $node->getAttribute('sortOrder') ?: 0;
        }

        array_multisort($types, $sortOrders);

        return $types;
    }

    /**
     * Convert projection
     *
     * @param array $interfaces
     * @param DOMDocument $source
     * @return array
     */
    private function convertProjection(array $interfaces, DOMDocument $source): array
    {
        $output = [];

        /** @var DOMNodeList $projectionNodes */
        $projectionNodes = $source->getElementsByTagName('projection');

        foreach ($projectionNodes as $projectionNode) {
            $forInterface = (string) $projectionNode->getAttribute('for');
            $this->assertInterfaceExists($interfaces, $forInterface);

            $output[$forInterface] = [];

            $fromNodes = $projectionNode->getElementsByTagName('from');
            foreach ($fromNodes as $fromNode) {
                $type = (string) $fromNode->getAttribute('type');
                $preprocessorNodes = $fromNode->getElementsByTagName('preprocessor');
                $postprocessorNodes = $fromNode->getElementsByTagName('postprocessor');
                $straightNodes = $fromNode->getElementsByTagName('straight');

                $straightMapping = [];
                foreach ($straightNodes as $straightNode) {
                    $from = (string) $straightNode->getAttribute('from');
                    $to = (string) $straightNode->getAttribute('to');

                    $straightMapping[$to] = $from;
                }

                $output[$forInterface][$type] = [
                    'preprocessor' => $this->extractProcessor($preprocessorNodes),
                    'postprocessor' => $this->extractProcessor($postprocessorNodes),
                    'straight' => $straightMapping,
                ];
            }
        }

        return $output;
    }

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
            'projection' => [],
        ];

        if (!$source instanceof DOMDocument) {
            return $output;
        }

        $output['interface'] = $this->convertInterface($source);
        $output['class'] = $this->convertClasses($output['interface'], $source);
        $output['projection'] = $this->convertProjection($output['interface'], $source);

        return $output;
    }
}
