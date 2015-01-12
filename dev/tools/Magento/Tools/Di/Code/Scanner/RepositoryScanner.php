<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Di\Code\Scanner;

/**
 * Class RepositoryScanner
 */
class RepositoryScanner implements ScannerInterface
{
    /**
     * Get array of class names
     *
     * @param array $files
     * @return array
     */
    public function collectEntities(array $files)
    {
        $repositoryClassNames = [];
        foreach ($files as $fileName) {
            $dom = new \DOMDocument();
            $dom->loadXML(file_get_contents($fileName));
            $xpath = new \DOMXPath($dom);
            /** @var $node \DOMNode */
            foreach ($xpath->query('//preference') as $node) {
                $forType = $node->attributes->getNamedItem('for');
                $replacementType = $node->attributes->getNamedItem('type');
                if (
                    !is_null($forType)
                    && !is_null($replacementType)
                    && (substr($forType->nodeValue, -19) == 'RepositoryInterface')
                ) {
                    if (!class_exists($replacementType->nodeValue)) {
                        $persistor = str_replace('\\Repository', 'InterfacePersistor', $replacementType->nodeValue);
                        $factory = str_replace('\\Repository', 'InterfaceFactory', $replacementType->nodeValue);
                        $dataBuilder = str_replace('\\Repository', 'DataBuilder', $replacementType->nodeValue);
                        $searchResultFactory
                            = str_replace('\\Repository', 'SearchResultInterfaceFactory', $replacementType->nodeValue);
                        $repositoryClassNames[$persistor] = $persistor;
                        $repositoryClassNames[$factory] = $factory;
                        $repositoryClassNames[$dataBuilder] = $dataBuilder;
                        $repositoryClassNames[$searchResultFactory] = $searchResultFactory;
                        $repositoryClassNames[$replacementType->nodeValue] = $replacementType->nodeValue;
                    }
                }
            }
        }
        return $repositoryClassNames;
    }
}
