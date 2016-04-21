<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Code\Scanner;

use Magento\Framework\Autoload\AutoloaderRegistry;

/**
 * Class RepositoryScanner
 */
class RepositoryScanner implements ScannerInterface
{
    /**
     * @var bool
     */
    private $useAutoload = true;

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
                    $forType !== null
                    && $replacementType !== null
                    && (substr($forType->nodeValue, -19) == 'RepositoryInterface')
                ) {
                    if (!class_exists($replacementType->nodeValue, false)
                        && !AutoloaderRegistry::getAutoloader()->loadClass($replacementType->nodeValue)) {
                        $persistor = str_replace('\\Repository', 'InterfacePersistor', $replacementType->nodeValue);
                        $factory = str_replace('\\Repository', 'InterfaceFactory', $replacementType->nodeValue);
                        $searchResultFactory
                            = str_replace('\\Repository', 'SearchResultInterfaceFactory', $replacementType->nodeValue);
                        $repositoryClassNames[$persistor] = $persistor;
                        $repositoryClassNames[$factory] = $factory;
                        $repositoryClassNames[$searchResultFactory] = $searchResultFactory;
                        $repositoryClassNames[$replacementType->nodeValue] = $replacementType->nodeValue;
                    }
                }
            }
        }
        return $repositoryClassNames;
    }

    /**
     * Sets autoload flag
     *
     * @param boolean $useAutoload
     * @return void
     */
    public function setUseAutoload($useAutoload)
    {
        $this->useAutoload = $useAutoload;
    }
}
