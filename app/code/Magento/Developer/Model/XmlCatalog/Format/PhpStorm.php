<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Model\XmlCatalog\Format;

use Magento\Framework\Exception\FileSystemException;
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
     * @var \Magento\Framework\Filesystem\File\WriteFactory
     */
    private $fileWriteFactory;

    /**
     * @param ReadFactory $readFactory
     * @param \Magento\Framework\Filesystem\File\WriteFactory $fileWriteFactory
     */
    public function __construct(
        ReadFactory $readFactory,
        \Magento\Framework\Filesystem\File\WriteFactory $fileWriteFactory
    ) {
        $this->currentDirRead = $readFactory->create(getcwd());
        $this->fileWriteFactory = $fileWriteFactory;
    }

    /**
     * Generate Catalog of URNs for the PhpStorm 9
     *
     * @param string[] $dictionary
     * @param string $configFilePath relative path to the PhpStorm misc.xml
     * @return void
     */
    public function generateCatalog(array $dictionary, $configFilePath)
    {
        $componentNode = null;
        $projectNode = null;

        try {
            $file = $this->fileWriteFactory->create(
                $configFilePath,
                \Magento\Framework\Filesystem\DriverPool::FILE,
                'r'
            );
            $dom = new \DOMDocument();
            $dom->loadXML($file->readAll());
            $xpath = new \DOMXPath($dom);
            $nodeList = $xpath->query('/project');
            $projectNode = $nodeList->item(0);
            $file->close();
        } catch (FileSystemException $f) {
            //create file if does not exists
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
        $file = $this->fileWriteFactory->create(
            $configFilePath,
            \Magento\Framework\Filesystem\DriverPool::FILE,
            'w'
        );
        $file->write($dom->saveXML());
        $file->close();
    }
}
