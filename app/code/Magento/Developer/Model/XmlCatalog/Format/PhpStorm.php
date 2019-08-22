<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Model\XmlCatalog\Format;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DomDocument\DomDocumentFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\File\WriteFactory;

/**
 * Class PhpStorm generates URN catalog for PhpStorm 9
 */
class PhpStorm implements FormatInterface
{
    private const PROJECT_PATH_IDENTIFIER = '$PROJECT_DIR$';

    /**
     * @var ReadInterface
     */
    private $currentDirRead;

    /**
     * @var WriteFactory
     */
    private $fileWriteFactory;

    /**
     * @var DomDocumentFactory
     */
    private $domDocumentFactory;

    /**
     * @param ReadFactory $readFactory
     * @param WriteFactory $fileWriteFactory
     * @param DomDocumentFactory $domDocumentFactory
     */
    public function __construct(
        ReadFactory $readFactory,
        WriteFactory $fileWriteFactory,
        DomDocumentFactory $domDocumentFactory = null
    ) {
        $this->currentDirRead = $readFactory->create(getcwd());
        $this->fileWriteFactory = $fileWriteFactory;
        $this->domDocumentFactory = $domDocumentFactory ?: ObjectManager::getInstance()->get(DomDocumentFactory::class);
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
            $dom = $this->domDocumentFactory->create();
            $fileContent = $file->readAll();
            if (!empty($fileContent)) {
                $dom->loadXML($fileContent);
            } else {
                $this->initEmptyFile($dom);
            }
            $xpath = new \DOMXPath($dom);
            $nodeList = $xpath->query('/project');
            $projectNode = $nodeList->item(0);
            $file->close();
        } catch (FileSystemException $f) {
            //create file if does not exists
            $dom = $this->domDocumentFactory->create();
            $projectNode = $this->initEmptyFile($dom);
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
            $node->setAttribute('location', $this->getFileLocationInProject($xsdPath));
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

    /**
     * Setup basic empty dom elements
     *
     * @param \DOMDocument $dom
     * @return \DOMElement
     */
    private function initEmptyFile(\DOMDocument $dom)
    {
        $projectNode = $dom->createElement('project');

        //PhpStorm 9 version for component is "4"
        $projectNode->setAttribute('version', '4');
        $dom->appendChild($projectNode);
        $rootComponentNode = $dom->createElement('component');

        //PhpStorm 9 version for ProjectRootManager is "2"
        $rootComponentNode->setAttribute('version', '2');
        $rootComponentNode->setAttribute('name', 'ProjectRootManager');
        $projectNode->appendChild($rootComponentNode);
        return $projectNode;
    }

    /**
     * Resolve xsdpath to xml project path
     *
     * @param string $xsdPath
     * @return string
     */
    private function getFileLocationInProject(string $xsdPath): string
    {
        return self::PROJECT_PATH_IDENTIFIER . DIRECTORY_SEPARATOR . $this->currentDirRead->getRelativePath($xsdPath);
    }
}
