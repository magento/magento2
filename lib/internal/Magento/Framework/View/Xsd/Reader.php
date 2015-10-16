<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Xsd;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\DirSearch;
use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\Filesystem;

class Reader implements \Magento\Framework\Config\ReaderInterface
{
    /**
     * @var string
     */
    protected $defaultScope;

    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var DirSearch
     */
    protected $componentDirSearch;

    /**
     * @var string
     */
    protected $searchFilesPattern;

    /** @var \Magento\Framework\Config\Dom\UrnResolver */
    protected $urnResolver;

    /**
     * @var FileIteratorFactory
     */
    private $iteratorFactory;

    /**
     * @param DirSearch $dirSearch
     * @param UrnResolver $urnResolver
     * @param FileIteratorFactory $iteratorFactory
     * @param string $fileName
     * @param string $defaultScope
     * @param string $searchFilesPattern
     */
    public function __construct(
        DirSearch $dirSearch,
        UrnResolver $urnResolver,
        FileIteratorFactory $iteratorFactory,
        $fileName,
        $defaultScope,
        $searchFilesPattern
    ) {
        $this->componentDirSearch = $dirSearch;
        $this->urnResolver = $urnResolver;
        $this->fileName = $fileName;
        $this->defaultScope = $defaultScope;
        $this->searchFilesPattern = $searchFilesPattern;
        $this->iteratorFactory = $iteratorFactory;
    }

    /**
     * Get list of xsd files
     *
     * @param string $filename
     * @return array
     */
    public function getListXsdFiles($filename)
    {
        return $this->iteratorFactory->create(
            array_merge(
                $this->componentDirSearch->collectFiles(ComponentRegistrar::MODULE, 'etc/' . $filename),
                $this->componentDirSearch->collectFiles(ComponentRegistrar::LIBRARY, '*/etc/' . $filename)
            )
        );
    }

    /**
     * Read xsd files from list
     *
     * @param null $scope
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function read($scope = null)
    {
        $fileList = $this->getListXsdFiles($this->fileName);
        if (!count($fileList)) {
            return [];
        }
        $mergeXsd = $this->readXsdFiles($fileList);

        return $mergeXsd;
    }

    /**
     * Get merged xsd file
     *
     * @param array $fileList
     * @param string $baseXsd
     * @return null|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function readXsdFiles($fileList, $baseXsd = null)
    {
        $baseXsd = new \DOMDocument();
        $baseXsdPath = $this->urnResolver->getRealPath($this->searchFilesPattern . $this->fileName);
        $baseXsd->load($baseXsdPath);
        $configMerge = null;
        foreach ($fileList as $key => $content) {
            if ($key == $baseXsdPath) {
                continue;
            }
            try {
                if (!empty($content)) {
                    if ($configMerge) {
                        $configMerge = $this->mergeXsd($configMerge, $content);
                    } else {
                        $configMerge = $this->mergeXsd($baseXsd->saveXML(), $content);
                    }
                }
            } catch (\Magento\Framework\Config\Dom\ValidationException $e) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase("Invalid XSD in file %1:\n%2", [$key, $e->getMessage()])
                );
            }
        }

        return $configMerge;
    }

    /**
     * Merge xsd files
     *
     * @param string $parent
     * @param string $child
     * @return string
     */
    protected function mergeXsd($parent, $child)
    {
        $domParent = $this->createDomInstance($parent);
        $domChild = $this->createDomInstance($child);
        $domChild = $domChild->documentElement;

        $domParentElement = $domParent->getElementsByTagName('complexType');
        $parentDomElements = $this->findDomElement($domParentElement, 'name');
        foreach ($parentDomElements->childNodes as $findElement) {
            if ($findElement instanceof \DOMElement) {
                $domParentNode = $findElement;
                break;
            }
        }
        $domChildElement = $domChild->getElementsByTagName('extension');
        $childDomElements = $this->findDomElement($domChildElement, 'base');
        $domParent = $this->addHeadChildIntoParent($childDomElements, $domParent, $domParentNode);
        $delete = $domChild->getElementsByTagName('redefine')->item(0);
        $domChild->removeChild($delete);
        $domParent = $this->addBodyChildIntoParent($domChild, $domParent);

        return $domParent->saveXML();
    }

    /**
     * Create DOM instance
     *
     * @param string $source
     * @return \DOMDocument
     */
    protected function createDomInstance($source)
    {
        $domInstance = new \DOMDocument('1.0', 'UTF-8');
        $domInstance->formatOutput = true;
        $domInstance->loadXML($source);
        $domInstance->preserveWhiteSpace = true;

        return $domInstance;
    }

    /**
     * Find searched element in DOM
     *
     * @param \DOMNodeList $domParentElement
     * @param string $attribute
     * @return mixed
     */
    protected function findDomElement(\DOMNodeList $domParentElement, $attribute)
    {
        foreach ($domParentElement as $child) {
            if ($child->getAttribute($attribute) === 'mediaType'
                && $child instanceof \DOMElement
                && $child->hasChildNodes()
            ) {
                return $child;
            }
        }
    }

    /**
     * Add into parent head elements from child
     *
     * @param \DOMElement $childDomElements
     * @param \DOMDocument $domParent
     * @param \DOMElement $domParentNode
     * @return \DOMDocument
     */
    protected function addHeadChildIntoParent(
        \DOMElement $childDomElements,
        \DOMDocument $domParent,
        \DOMElement $domParentNode
    ) {
        foreach ($childDomElements->childNodes as $sequence) {
            if ($sequence instanceof \DOMElement && $sequence->hasChildNodes()) {
                foreach ($sequence->childNodes as $findElement) {
                    if ($findElement instanceof \DOMElement) {
                        $importedNodes = $domParent->importNode($findElement, true);
                        $domParentNode->appendChild($importedNodes);
                    }
                }
            }
        }

        return $domParent;
    }

    /**
     * Add into parent body elements from child
     *
     * @param \DOMElement $domChild
     * @param \DOMDocument $domParent
     * @return \DOMDocument
     */
    protected function addBodyChildIntoParent(\DOMElement $domChild, \DOMDocument $domParent)
    {
        foreach ($domChild->childNodes as $node) {
            $importNode = $domParent->importNode($node, true);
            $domParent->documentElement->appendChild($importNode);
        }

        return $domParent;
    }
}
