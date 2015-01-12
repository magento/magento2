<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Export\Config;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     * @throws \InvalidArgumentException
     */
    public function convert($source)
    {
        $output = ['entities' => [], 'fileFormats' => []];
        /** @var \DOMNodeList $entities */
        $entities = $source->getElementsByTagName('entity');
        /** @var \DOMNode $entityConfig */
        foreach ($entities as $entityConfig) {
            $attributes = $entityConfig->attributes;
            $name = $attributes->getNamedItem('name')->nodeValue;
            $label = $attributes->getNamedItem('label')->nodeValue;
            $model = $attributes->getNamedItem('model')->nodeValue;
            $entityAttributeFilterType = $attributes->getNamedItem('entityAttributeFilterType')->nodeValue;

            $output['entities'][$name] = [
                'name' => $name,
                'label' => $label,
                'model' => $model,
                'types' => [],
                'entityAttributeFilterType' => $entityAttributeFilterType,
            ];
        }

        /** @var \DOMNodeList $entityTypes */
        $entityTypes = $source->getElementsByTagName('entityType');
        /** @var \DOMNode $entityTypeConfig */
        foreach ($entityTypes as $entityTypeConfig) {
            $attributes = $entityTypeConfig->attributes;
            $model = $attributes->getNamedItem('model')->nodeValue;
            $name = $attributes->getNamedItem('name')->nodeValue;
            $entity = $attributes->getNamedItem('entity')->nodeValue;

            if (isset($output['entities'][$entity])) {
                $output['entities'][$entity]['types'][$name] = ['name' => $name, 'model' => $model];
            }
        }

        /** @var \DOMNodeList $fileFormats */
        $fileFormats = $source->getElementsByTagName('fileFormat');
        /** @var \DOMNode $fileFormatConfig */
        foreach ($fileFormats as $fileFormatConfig) {
            $attributes = $fileFormatConfig->attributes;
            $name = $attributes->getNamedItem('name')->nodeValue;
            $model = $attributes->getNamedItem('model')->nodeValue;
            $label = $attributes->getNamedItem('label')->nodeValue;

            $output['fileFormats'][$name] = ['name' => $name, 'model' => $model, 'label' => $label];
        }
        return $output;
    }
}
