<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Config;

use Magento\Framework\Filesystem;
use Magento\Framework\Config\ConverterInterface;
use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\ReadInterface;

/**
 * Class Reader
 */
class Reader implements UiReaderInterface
{
    /**
     * DOM document merger
     *
     * @var DomMergerInterface
     */
    protected $domMerger;

    /**
     * XML converter
     *
     * @var ConverterInterface
     */
    protected $converter;

    /**
     * Constructor
     *
     * @param FileCollectorInterface $fileCollector
     * @param ConverterInterface $converter
     * @param DomMergerInterface $domMerger
     */
    public function __construct(
        FileCollectorInterface $fileCollector,
        ConverterInterface $converter,
        DomMergerInterface $domMerger
    ) {
        $this->converter = $converter;
        $this->domMerger = $domMerger;
        $this->readFiles($fileCollector->collectFiles());
    }

    /**
     * Read configuration files
     *
     * @param array $fileList
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function readFiles(array $fileList)
    {
        foreach ($fileList as $fileContent) {
            $this->domMerger->merge($fileContent);
        }
    }

    /**
     * Add xml content in the merged file
     *
     * @param string $xmlContent
     * @return void
     */
    public function addXMLContent($xmlContent)
    {
        $this->domMerger->merge($xmlContent);
    }

    /**
     * Add DOM node into DOM document
     *
     * @param \DOMNode $node
     * @return void
     */
    public function addNode(\DOMNode $node)
    {
        $this->domMerger->mergeNode($node);
    }

    /**
     * Load configuration scope
     *
     * @param string|null $scope
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function read($scope = null)
    {
        return $this->converter->convert($this->domMerger->getDom());
    }

    /**
     * Get content from the merged files
     *
     * @return string
     */
    public function getContent()
    {
        return $this->domMerger->getDom()->saveXML();
    }

    /**
     * Get DOM document
     *
     * @return \DOMDocument
     */
    public function getDOMDocument()
    {
        return $this->domMerger->getDom();
    }
}
