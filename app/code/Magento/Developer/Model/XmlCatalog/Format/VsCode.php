<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare (strict_types = 1);

namespace Magento\Developer\Model\XmlCatalog\Format;

use Magento\Framework\DomDocument\DomDocumentFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\WriteFactory;

/**
 * Class VsCode generates URN catalog for VsCode
 */
class VsCode implements FormatInterface
{
    private const PROJECT_PATH_IDENTIFIER = '..';
    private const FILE_MODE_READ = 'r';
    private const FILE_MODE_WRITE = 'w';

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
        $this->domDocumentFactory = $domDocumentFactory;
    }

    /**
     * Generate Catalog of URNs for the VsCode
     *
     * @param string[] $dictionary
     * @param string $configFile relative path to the VsCode catalog.xml
     * @return void
     */
    public function generateCatalog(array $dictionary, $configFile): void
    {
        $catalogNode = null;

        try {
            $file = $this->fileWriteFactory->create($configFile, DriverPool::FILE, self::FILE_MODE_READ);
            $dom = $this->domDocumentFactory->create();
            $fileContent = $file->readAll();
            if (!empty($fileContent)) {
                $dom->loadXML($fileContent);
            } else {
                $this->initEmptyFile($dom);
            }
            $xpath = new \DOMXPath($dom);
            $nodeList = $xpath->query('/catalog');
            $catalogNode = $nodeList->item(0);
            $file->close();
        } catch (FileSystemException $f) {
            //create file if does not exists
            $dom = $this->domDocumentFactory->create();
            $catalogNode = $this->initEmptyFile($dom);
        }

        foreach ($dictionary as $urn => $xsdPath) {
            $node = $dom->createElement('system');
            $node->setAttribute('systemId', $urn);
            $node->setAttribute('uri', $this->getFileLocationInProject($xsdPath));
            $catalogNode->appendChild($node);
        }
        $dom->formatOutput = true;
        $file = $this->fileWriteFactory->create($configFile, DriverPool::FILE, self::FILE_MODE_WRITE);
        $file->write($dom->saveXML());
        $file->close();
    }

    /**
     * Setup basic empty dom elements
     *
     * @param \DOMDocument $dom
     * @return \DOMElement
     */
    private function initEmptyFile(\DOMDocument $dom): \DOMElement
    {
        $catalogNode = $dom->createElement('catalog');

        $catalogNode->setAttribute('xmlns', 'urn:oasis:names:tc:entity:xmlns:xml:catalog');
        $dom->appendChild($catalogNode);

        return $catalogNode;
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
