<?php
declare(strict_types=1);

namespace Magento\Framework\Dto\Config;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Config\ConverterInterface;

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
            $dtoClass = $dto->getAttribute('class');
            $isMutable = filter_var($dto->getAttribute('mutable'), FILTER_VALIDATE_BOOLEAN);

            $interface = $dto->getAttribute('interface');

            $output[$dtoClass] = [
                'mutable' => $isMutable,
                'type' => 'class',
                'interface' => $dto->getAttribute('interface'),
                'properties' => []
            ];

            if ($interface) {
                $output[$interface] = [
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

                if ($interface) {
                    $output[$interface]['properties'][$propertyId] = [
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
