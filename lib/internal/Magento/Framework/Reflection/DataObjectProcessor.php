<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Reflection;

use Magento\Framework\Api\DtoProcessor;
use Magento\Framework\App\ObjectManager;

/**
 * Data object processor for array serialization using class reflection
 *
 * @api
 */
class DataObjectProcessor
{
    /**
     * @var array
     */
    private $processors;

    /**
     * @var DtoProcessor
     */
    private $dtoProcessor;

    /**
     * @param MethodsMap $methodsMapProcessor
     * @param TypeCaster $typeCaster
     * @param FieldNamer $fieldNamer
     * @param CustomAttributesProcessor $customAttributesProcessor
     * @param ExtensionAttributesProcessor $extensionAttributesProcessor
     * @param DtoProcessor|null $dataTransportHelper
     * @param array $processors
     */
    public function __construct(
        MethodsMap $methodsMapProcessor,
        TypeCaster $typeCaster,
        FieldNamer $fieldNamer,
        CustomAttributesProcessor $customAttributesProcessor,
        ExtensionAttributesProcessor $extensionAttributesProcessor,
        array $processors = [],
        DtoProcessor $dataTransportHelper = null
    ) {
        $this->processors = $processors;
        $this->dtoProcessor = $dataTransportHelper ?:
            ObjectManager::getInstance()->get(DtoProcessor::class);
    }

    /**
     * Use class reflection on given data interface to build output data array
     *
     * @param mixed $dataObject
     * @param string $dataObjectType
     * @return array
     */
    public function buildOutputDataArray($dataObject, $dataObjectType)
    {
        $outputData = $this->dtoProcessor->getObjectData($dataObject, $dataObjectType);
        return $this->changeOutputArray($dataObject, $outputData);
    }

    /**
     * Change output array if needed.
     *
     * @param mixed $dataObject
     * @param array $outputData
     * @return array
     */
    private function changeOutputArray($dataObject, array $outputData): array
    {
        foreach ($this->processors as $dataObjectClassName => $processor) {
            if ($dataObject instanceof $dataObjectClassName) {
                $outputData = $processor->execute($dataObject, $outputData);
            }
        }

        return $outputData;
    }
}
