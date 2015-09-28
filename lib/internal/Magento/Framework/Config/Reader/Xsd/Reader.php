<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Reader\Xsd;

use Magento\Framework\Filesystem;
use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Reader implements \Magento\Framework\Config\ReaderInterface
{
    /*
     * @var string
     */
    protected $defaultScope;

    /*
     * @var string
     */
    protected $fileName;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $directoryRead;

    /**
     * @var \Magento\Framework\Config\FileIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * @param Filesystem $filesystem
     * @param FileIteratorFactory $iteratorFactory
     * @param string $fileName
     * @param string $defaultScope
     */
    public function __construct(
        Filesystem $filesystem,
        FileIteratorFactory $iteratorFactory,
        $fileName = 'view.xsd',
        $defaultScope = 'global'
    ) {
        $this->directoryRead = $filesystem->getDirectoryRead(DirectoryList::MODULES);
        $this->iteratorFactory = $iteratorFactory;
        $this->fileName = $fileName;
        $this->defaultScope = $defaultScope;
    }

    /**
     * Get list of xsd files
     *
     * @param $filename
     * @return \Magento\Framework\Config\FileIterator
     */
    public function getListXsdFiles($filename)
    {
        $iterator = $this->iteratorFactory->create(
            $this->directoryRead,
            $this->directoryRead->search('/*/*/etc/' . $filename)
        );
        return $iterator;
    }

    /**
     * Read xsd files from list
     *
     * @param null $scope
     * @return array|\Magento\Framework\Config\FileIterator
     * @throws \Magento\Framework\Exception\LocalizedException
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
        $baseXsd->load(__DIR__ . '/../../etc/' . $this->fileName);
        $configMerge = null;
        foreach ($fileList as $key => $content) {
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
     * @param $parent
     * @param $child
     * @return string
     */
    function mergeXsd($parent, $child)
    {
        $domParent = new \DOMDocument("1.0", 'UTF-8');
        $domParent->formatOutput = true;
        $domParent->loadXML($parent);

        $domChild = new \DOMDocument("1.0", 'UTF-8');
        $domChild->formatOutput = true;
        $domChild->loadXML($child);

        $res1 = $domParent->getElementsByTagName('choice')->item(0);
        $items2 = $domChild->getElementsByTagName('element')->item(1);
        $item1 = $domParent->importNode($items2, true);
        $res1->appendChild($item1);
        $domChild = $domChild->documentElement;
        $delete = $domChild->getElementsByTagName('element')->item(0);
        $domChild->removeChild($delete);

        foreach ($domChild->childNodes as $node) {
            $importNode = $domParent->importNode($node, true);
            $domParent->documentElement->appendChild($importNode);
        }

        return $domParent->saveXML();
    }

}
