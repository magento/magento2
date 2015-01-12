<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Import\Config;

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
        $output = ['entities' => []];
        /** @var \DOMNodeList $entities */
        $entities = $source->getElementsByTagName('entity');
        /** @var \DOMNode $entityConfig */
        foreach ($entities as $entityConfig) {
            $attributes = $entityConfig->attributes;
            $name = $attributes->getNamedItem('name')->nodeValue;
            $label = $attributes->getNamedItem('label')->nodeValue;
            $behaviorModel = $attributes->getNamedItem('behaviorModel')->nodeValue;
            $model = $attributes->getNamedItem('model')->nodeValue;

            $output['entities'][$name] = [
                'name' => $name,
                'label' => $label,
                'behaviorModel' => $behaviorModel,
                'model' => $model,
                'types' => [],
                'relatedIndexers' => [],
            ];
        }

        /** @var \DOMNodeList $entityTypes */
        $entityTypes = $source->getElementsByTagName('entityType');
        /** @var \DOMNode $entityTypeConfig */
        foreach ($entityTypes as $entityTypeConfig) {
            $attributes = $entityTypeConfig->attributes;
            $name = $attributes->getNamedItem('name')->nodeValue;
            $model = $attributes->getNamedItem('model')->nodeValue;
            $entity = $attributes->getNamedItem('entity')->nodeValue;

            if (isset($output['entities'][$entity])) {
                $output['entities'][$entity]['types'][$name] = ['name' => $name, 'model' => $model];
            }
        }

        /** @var \DOMNodeList $relatedIndexers */
        $relatedIndexers = $source->getElementsByTagName('relatedIndexer');
        /** @var \DOMNode $relatedIndexerConfig */
        foreach ($relatedIndexers as $relatedIndexerConfig) {
            $attributes = $relatedIndexerConfig->attributes;
            $name = $attributes->getNamedItem('name')->nodeValue;
            $entity = $attributes->getNamedItem('entity')->nodeValue;

            if (isset($output['entities'][$entity])) {
                $output['entities'][$entity]['relatedIndexers'][$name] = ['name' => $name];
            }
        }
        return $output;
    }
}
