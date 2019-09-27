<?php
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
        $output = [];
        if (!$source instanceof DOMDocument) {
            return $output;
        }

        /** @var DOMNodeList $dtos */
        $dtos = $source->getElementsByTagName('dto');

        /** @var DOMNode $dto */
        foreach ($dtos as $dto) {
            $dtoId = $dto->getAttribute('id');

            $dtoClassNodes = $dto->getElementsByTagName('class');
            $dtoInterfaceNodes = $dto->getElementsByTagName('interface');

            if (count($dtoClassNodes) !== 1) {
                throw new InvalidArgumentException('DTO ' . $dtoId . ' must have a class definition');
            }
            if (count($dtoInterfaceNodes) !== 1) {
                throw new InvalidArgumentException('DTO ' . $dtoId . ' must have an interface definition');
            }

            $dtoClass = $dtoClassNodes[0]->nodeValue;
            $dtoInterface = $dtoInterfaceNodes[0]->nodeValue;

            $isMutable = filter_var($dto->getAttribute('mutable'), FILTER_VALIDATE_BOOLEAN);

            $output[$dtoClass] = [
                'mutable' => $isMutable,
                'type' => 'class',
                'interface' => $dtoInterface,
                'properties' => []
            ];

            if ($dtoInterface) {
                $output[$dtoInterface] = [
                    'type' => 'interface',
                    'mutable' => $isMutable,
                    'class' => $dtoClass,
                    'properties' => []
                ];
            }

            $properties = $dto->getElementsByTagName('property');
            foreach ($properties as $property) {
                $propertyId = SimpleDataObjectConverter::snakeCaseToCamelCase($property->getAttribute('name'));
                $propertyType = $property->getAttribute('type');
                $propertyOptional = filter_var($property->getAttribute('optional'), FILTER_VALIDATE_BOOLEAN);
                $propertyNullable = filter_var($property->getAttribute('nullable'), FILTER_VALIDATE_BOOLEAN);

                $output[$dtoClass]['properties'][$propertyId] = [
                    'type' => $propertyType,
                    'optional' => $propertyOptional,
                    'nullable' => $propertyNullable
                ];

                if ($dtoInterface) {
                    $output[$dtoInterface]['properties'][$propertyId] = [
                        'type' => $propertyType,
                        'optional' => $propertyOptional,
                        'nullable' => $propertyNullable
                    ];
                }
            }
        }

        return $output;
    }
}
