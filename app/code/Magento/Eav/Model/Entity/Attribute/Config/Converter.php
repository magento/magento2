<?php
/**
 * Attributes configuration converter
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute\Config;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Convert config
     *
     * @param mixed $source
     * @return array
     */
    public function convert($source)
    {
        $output = [];

        /** @var \DOMNodeList $entities */
        $entities = $source->getElementsByTagName('entity');

        /** @var DOMNode $entity */
        foreach ($entities as $entity) {
            $entityConfig = [];
            $attributes = [];

            /** @var DOMNode $entityAttribute */
            foreach ($entity->getElementsByTagName('attribute') as $entityAttribute) {
                $attributeFields = [];
                foreach ($entityAttribute->getElementsByTagName('field') as $fieldData) {
                    $locked = $fieldData->attributes->getNamedItem('locked')->nodeValue == "true" ? true : false;
                    $attributeFields[$fieldData->attributes->getNamedItem(
                        'code'
                    )->nodeValue] = [
                        'code' => $fieldData->attributes->getNamedItem('code')->nodeValue,
                        'locked' => $locked,
                    ];
                }
                $attributes[$entityAttribute->attributes->getNamedItem('code')->nodeValue] = $attributeFields;
            }
            $entityConfig['attributes'] = $attributes;
            $output[$entity->attributes->getNamedItem('type')->nodeValue] = $entityConfig;
        }

        return $output;
    }
}
