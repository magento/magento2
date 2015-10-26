<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Model\XmlCatalog\Format;

/**
 * Class PhpStormNine generates URN catalog for PhpStorm 9
 */
class PhpStorm implements FormatInterface
{
    /**
     * Generate Catalog of URNs for the PhpStorm 9
     *
     * @param string[] $dictionary
     * @param string $path
     * @return void
     */
    public function generateCatalog(array $dictionary, $path)
    {
        $componentNode = null;
        $projectNode = null;
        if (file_exists($path)) {
            $dom = new \DOMDocument();
            $dom->load($path);
            $xpath = new \DOMXPath($dom);
            $nodeList = $xpath->query('/project');
            $projectNode = $nodeList->item(0);
        } else {
            $dom = new \DOMDocument();
            $projectNode = $dom->createElement('project');

            //PhpStorm 9 version for component is "4"
            $projectNode->setAttribute('version', '4');
            $dom->appendChild($projectNode);
            $rootComponentNode = $dom->createElement('component');

            //PhpStorm 9 version for ProjectRootManager is "2"
            $rootComponentNode->setAttribute('version', '2');
            $rootComponentNode->setAttribute('name', 'ProjectRootManager');
            $projectNode->appendChild($rootComponentNode);

        }

        $xpath = new \DOMXPath($dom);
        $nodeList = $xpath->query("/project/component[@name='ProjectResources']");
        $componentNode = $nodeList->item(0);
        if ($componentNode == null) {
            $componentNode = $dom->createElement('component');
            $componentNode->setAttribute('name', 'ProjectResources');
            $projectNode->appendChild($componentNode);
        }

        foreach ($dictionary as $urn => $xsdPath) {
            $node = $dom->createElement('resource');
            $node->setAttribute('url', $urn);
            $node->setAttribute('location', $xsdPath);
            $componentNode->appendChild($node);
        }
        $dom->formatOutput = true;
        file_put_contents($path, $dom->saveXML(), FILE_TEXT);
    }
}