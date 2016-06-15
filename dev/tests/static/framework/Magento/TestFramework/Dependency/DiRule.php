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
use Magento\TestFramework\Dependency\Virtual\Mapper;

class DiRule implements RuleInterface
{
    /**
     * @var Mapper
     */
    private $mapper;

    /**
     * @param Mapper $mapper
     */
    public function __construct(Mapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * @var array
     */
    private static $tagNameMap = [
        'type' => 'name',
        'preference' => [
            'type',
            'for'
        ],
        'plugin' => 'type',
        'virtualType' => 'type'
    ];

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
        foreach ($this->fetchPossibleDependencies($contents) as $type => $possibleDependencies) {
            foreach ($possibleDependencies as $possibleDependency) {
                if (preg_match($pattern, $possibleDependency, $matches)) {
                    $referenceModule = str_replace('_', '\\', $matches['module']);
                    if ($currentModule == $referenceModule) {
                        continue;
                    }
                    $dependenciesInfo[] = [
                        'module' => $referenceModule,
                        'type' => $type,
                        'source' => $matches['class'],
                    ];
                }
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

        foreach (self::$tagNameMap as $tagName => $attributeNames) {
            if (is_string($attributeNames)) {
                $attributeNames = [$attributeNames];
            }
            $nodes = $doc->getElementsByTagName($tagName);
            /** @var \DOMElement $type */
            foreach ($nodes as $node) {
                foreach ($attributeNames as $attributeName) {
                    $possibleDependencies[RuleInterface::TYPE_SOFT][] = $node->getAttribute($attributeName);
                }
            }
        }

        $xpath = new DOMXPath($doc);
        //$textNodes = $xpath->query('//*[contains(text(),\'Magento\')]');
        $textNodes = $xpath->query('//*[@xsi:type="object"]');
        /** @var \DOMElement $node */
        foreach ($textNodes as $node) {
            $possibleDependencies[RuleInterface::TYPE_HARD][] = $node->nodeValue;
        }
        return $possibleDependencies;
    }
}
