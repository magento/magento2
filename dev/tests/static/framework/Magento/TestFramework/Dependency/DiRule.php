<?php
/**
 * Rule for searching php file dependency
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Dependency;

use DOMDocument;
use DOMXPath;
use Magento\Framework\App\Utility\Files;
use Magento\TestFramework\Dependency\VirtualType\VirtualTypeMapper;

class DiRule implements RuleInterface
{
    /**
     * @var VirtualTypeMapper
     */
    private $mapper;

    /**
     * @var string
     */
    private $pattern;

    /**
     * @param VirtualTypeMapper $mapper
     */
    public function __construct(VirtualTypeMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function getPattern()
    {
        if ($this->pattern === null) {
            $this->pattern = '~\b(?<class>(?<module>('
                . implode('_|', Files::init()->getNamespaces())
                . '[_\\\\])[a-zA-Z0-9]+)[a-zA-Z0-9_\\\\]*)\b~';
        }

        return $this->pattern;
    }

    /**
     * @var array
     */
    private static $tagNameMap = [
        'type' => ['name'],
        'preference' => [
            'type',
            'for'
        ],
        'plugin' => ['type'],
        'virtualType' => ['type']
    ];

    /**
     * Gets alien dependencies information for current module by analyzing file's contents
     *
     * @param string $currentModule
     * @param string $fileType
     * @param string $file
     * @param string $contents
     * @return array
     * @throws \Exception
     */
    public function getDependencyInfo($currentModule, $fileType, $file, &$contents)
    {
        if (pathinfo($file, PATHINFO_BASENAME) !== 'di.xml') {
            return [];
        }

        $dependenciesInfo = [];
        $scope = $this->mapper->getScopeFromFile($file);
        foreach ($this->fetchPossibleDependencies($contents) as $type => $deps) {
            foreach ($deps as $dep) {
                $dep = $this->mapper->getType($dep, $scope);

                if (preg_match($this->getPattern(), $dep, $matches)) {
                    $referenceModule = str_replace('_', '\\', $matches['module']);
                    if ($currentModule === $referenceModule) {
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
        $doc = new DOMDocument();
        $doc->loadXML($contents);

        return [
            RuleInterface::TYPE_SOFT => $this->getSoftDependencies($doc),
            RuleInterface::TYPE_HARD => $this->getHardDependencies($doc)
        ];
    }

    /**
     * @param DOMDocument $doc
     * @return array
     */
    private function getSoftDependencies(DOMDocument $doc)
    {
        $result = [];
        foreach (self::$tagNameMap as $tagName => $attributeNames) {
            $nodes = $doc->getElementsByTagName($tagName);
            /** @var \DOMElement $node */
            foreach ($nodes as $node) {
                foreach ($attributeNames as $attributeName) {
                    $result[] = $node->getAttribute($attributeName);
                }
            }
        }

        return $result;
    }

    /**
     * @param DOMDocument $doc
     * @return array
     */
    private function getHardDependencies(DOMDocument $doc)
    {
        $result = [];
        $xpath = new DOMXPath($doc);
        $textNodes = $xpath->query('//*[@xsi:type="object"]');
        /** @var \DOMElement $node */
        foreach ($textNodes as $node) {
            $result[] = $node->nodeValue;
        }

        return $result;
    }
}
