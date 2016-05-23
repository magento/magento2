<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Compiler;

use Magento\Framework\DataObject;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\Compiler\Element\ElementInterface;

/**
 * Class IncludeElement
 */
class IncludeElement implements ElementInterface
{
    const INCLUDE_PATH = 'path';

    /**
     * @var Reader
     */
    protected $moduleReader;

    /**
     * @var Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * Constructor
     *
     * @param Reader $moduleReader
     * @param Filesystem\Directory\ReadFactory $readFactory
     */
    public function __construct(Reader $moduleReader, Filesystem\Directory\ReadFactory $readFactory)
    {
        $this->readFactory = $readFactory;
        $this->moduleReader = $moduleReader;
    }

    /**
     * Compiles the Element node
     *
     * @param CompilerInterface $compiler
     * @param \DOMElement $node
     * @param DataObject $processedObject
     * @param DataObject $context
     * @return void
     */
    public function compile(
        CompilerInterface $compiler,
        \DOMElement $node,
        DataObject $processedObject,
        DataObject $context
    ) {
        $ownerDocument = $node->ownerDocument;
        $parentNode = $node->parentNode;

        $document = new \DOMDocument();
        $document->loadXML($this->getContent($node->getAttribute(static::INCLUDE_PATH)));

        foreach ($this->getChildNodes($document->documentElement) as $child) {
            $compiler->compile($child, $processedObject, $context);
        }

        $newFragment = $ownerDocument->createDocumentFragment();
        foreach ($document->documentElement->childNodes as $child) {
            $newFragment->appendXML($document->saveXML($child));
        }

        $parentNode->replaceChild($newFragment, $node);
    }

    /**
     * Get child nodes
     *
     * @param \DOMElement $node
     * @return \DOMElement[]
     */
    protected function getChildNodes(\DOMElement $node)
    {
        $childNodes = [];
        foreach ($node->childNodes as $child) {
            $childNodes[] = $child;
        }

        return $childNodes;
    }

    /**
     * Get content of include file (in adminhtml area)
     *
     * @param string $includePath
     * @return string
     * @throws LocalizedException
     */
    protected function getContent($includePath)
    {
        // <include path="Magento_Payment::my_payment.xml" />
        list($moduleName, $filename) = explode('::', $includePath);

        $path = 'adminhtml/' . $filename;
        $directoryRead = $this->readFactory->create(
            $this->moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, $moduleName)
        );

        if ($directoryRead->isExist($path) && $directoryRead->isFile($path)) {
            return $directoryRead->readFile($path);
        }

        throw new LocalizedException(__('The file "%1" does not exist', $path));
    }
}
