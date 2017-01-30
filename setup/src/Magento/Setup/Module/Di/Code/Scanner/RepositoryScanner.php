<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
                    && (substr($forType->nodeValue, -19) === 'RepositoryInterface')
                ) {
                    // backward compatibility workaround for composer below 1.3.0
                    // (https://github.com/composer/composer/issues/5923)
                    $nodeValue = ltrim($replacementType->nodeValue, '\\');
                    if (!class_exists($nodeValue, false)
                        && !AutoloaderRegistry::getAutoloader()->loadClass($nodeValue)
                    ) {
                        $persistor = str_replace('\\Repository', 'InterfacePersistor', $nodeValue);
                        $factory = str_replace('\\Repository', 'InterfaceFactory', $nodeValue);
                        $searchResultFactory = str_replace('\\Repository', 'SearchResultInterfaceFactory', $nodeValue);
                        $repositoryClassNames[$persistor] = $persistor;
                        $repositoryClassNames[$factory] = $factory;
                        $repositoryClassNames[$searchResultFactory] = $searchResultFactory;
                        $repositoryClassNames[$nodeValue] = $nodeValue;
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
