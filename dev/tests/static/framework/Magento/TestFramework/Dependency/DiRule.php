<?php
/**
 * Rule for searching php file dependency
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Dependency;

use DOMDocument;
use DOMXPath;
use Magento\Framework\App\Utility\Files;

class DiRule implements RuleInterface
{
    /**
     * Gets alien dependencies information for current module by analyzing file's contents
     *
     * @param string $currentModule
     * @param string $fileType
     * @param string $file
     * @param string $contents
     * @return array
     */
    public function getDependencyInfo($currentModule, $fileType, $file, &$contents)
    {
        if (pathinfo($file, PATHINFO_BASENAME) !== 'di.xml') {
            return [];
        }

        $pattern = '~\b(?<class>(?<module>(' . implode(
                '_|',
                Files::init()->getNamespaces()
        ) . '[_\\\\])[a-zA-Z0-9]+)[a-zA-Z0-9_\\\\]*)\b~';

        $dependenciesInfo = [];
        foreach ($this->fetchPossibleDependencies($contents) as $possibleDependency) {
            if (preg_match($pattern, $possibleDependency, $matches)) {
                $referenceModule = str_replace('_', '\\', $matches['module']);
                if ($currentModule == $referenceModule) {
                    continue;
                }
                $dependenciesInfo[] = [
                    'module' => $referenceModule,
                    'type' => RuleInterface::TYPE_SOFT,
                    'source' => $matches['class'],
                ];
            }
        }
        return $dependenciesInfo;
    }

    /**
     * @param string $contents
     * @return array
     */
    private function fetchPossibleDependencies($contents)
    {
        $possibleDependencies = [];
        $doc = new DOMDocument();
        $doc->loadXML($contents);

        $typeNodes = $doc->getElementsByTagName('type');
        /** @var \DOMElement $type */
        foreach ($typeNodes as $type) {
            $possibleDependencies[] = $type->getAttribute('name');
        }

        $xpath = new DOMXPath($doc);
        $textNodes = $xpath->query('//*[contains(text(),\'Magento\')]');
        /** @var \DOMElement $argument */
        foreach ($textNodes as $node) {
            $possibleDependencies[] = $node->nodeValue;
        }
        return $possibleDependencies;
    }
}
