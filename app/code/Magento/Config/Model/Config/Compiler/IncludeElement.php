<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Compiler;

use Magento\Framework\Object;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Filesystem\DirectoryList;
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
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Constructor
     *
     * @param Reader $moduleReader
     * @param Filesystem $filesystem
     */
    public function __construct(Reader $moduleReader, Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->moduleReader = $moduleReader;
    }

    /**
     * Compiles the Element node
     *
     * @param CompilerInterface $compiler
     * @param \DOMElement $node
     * @param Object $processedObject
     * @param Object $context
     * @return void
     */
    public function compile(CompilerInterface $compiler, \DOMElement $node, Object $processedObject, Object $context)
    {
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
     * Get content include file (in adminhtml area)
     *
     * @param string $includePath
     * @return string
     * @throws LocalizedException
     */
    protected function getContent($includePath)
    {
        $modulesDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MODULES);

        // <include path="Magento_Payment::my_payment.xml" />
        list($moduleName, $filename) = explode('::', $includePath);

        $file = $this->moduleReader->getModuleDir('etc', $moduleName) . '/adminhtml/' . $filename;
        $path = $modulesDirectory->getRelativePath($file);

        if ($modulesDirectory->isExist($path) && $modulesDirectory->isFile($path)) {
            return $modulesDirectory->readFile($path);
        }

        throw new LocalizedException(__('The file "' . $path . '" does not exist'));
    }
}
