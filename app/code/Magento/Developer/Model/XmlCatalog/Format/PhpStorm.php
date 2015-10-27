<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Model\XmlCatalog\Format;

use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Class PhpStorm generates URN catalog for PhpStorm 9
 */
class PhpStorm implements FormatInterface
{
    /**
     * @var ReadInterface
     */
    private $currentDirRead;

    /**
     * @var WriteInterface
     */
    private $currentDirWrite;

    /**
     * @param ReadFactory $readFactory
     * @param WriteFactory $writeFactory
     */
    public function __construct(
        ReadFactory $readFactory,
        WriteFactory $writeFactory
    ) {
        $this->currentDirRead = $readFactory->create(getcwd());
        $this->currentDirWrite = $writeFactory->create(getcwd());
    }

    /**
     * Generate Catalog of URNs for the PhpStorm 9
     *
     * @param string[] $dictionary
     * @param string $configFile absolute path to the PhpStorm misc.xml
     * @return void
     */
    public function generateCatalog(array $dictionary, $configFile)
    {
        $componentNode = null;
        $projectNode = null;
        $configFile = $this->currentDirRead->getRelativePath($configFile);
        if ($this->currentDirRead->isExist($configFile) && $this->currentDirRead->isFile($configFile)) {
            $dom = new \DOMDocument();
            $dom->load($configFile);
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
        $this->currentDirWrite->writeFile($configFile, $dom->saveXML());
    }
}
