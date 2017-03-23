<?php
/**
 * Backend System Configuration reader.
 * Retrieves system configuration form layout from system.xml files. Merges configuration and caches it.
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface;

/**
 * Class Reader
 */
class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * List of identifier attributes for merging
     *
     * @var array
     */
    protected $_idAttributes = [
        '/config/system/tab' => 'id',
        '/config/system/section' => 'id',
        '/config/system/section(/group)+' => 'id',
        '/config/system/section(/group)+/field' => 'id',
        '/config/system/section(/group)+/field/depends/field' => 'id',
        '/config/system/section(/group)+/field/options/option' => 'label',
    ];

    /**
     * @var CompilerInterface
     */
    protected $compiler;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param Converter $converter
     * @param \Magento\Config\Model\Config\SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param CompilerInterface $compiler
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        Converter $converter,
        \Magento\Config\Model\Config\SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        CompilerInterface $compiler,
        $fileName = 'system.xml',
        $idAttributes = [],
        $domDocumentClass = \Magento\Framework\Config\Dom::class,
        $defaultScope = 'global'
    ) {
        $this->compiler = $compiler;
        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            $fileName,
            $idAttributes,
            $domDocumentClass,
            $defaultScope
        );
    }

    /**
     * Read configuration files
     *
     * @param array $fileList
     * @return array
     * @throws LocalizedException
     */
    protected function _readFiles($fileList)
    {

        /** @var \Magento\Framework\Config\Dom $configMerger */
        $configMerger = null;
        foreach ($fileList as $key => $content) {
            try {
                $content = $this->processingDocument($content);
                if (!$configMerger) {
                    $configMerger = $this->_createConfigMerger($this->_domDocumentClass, $content);
                } else {
                    $configMerger->merge($content);
                }
            } catch (\Magento\Framework\Config\Dom\ValidationException $e) {
                throw new LocalizedException(
                    new \Magento\Framework\Phrase("Invalid XML in file %1:\n%2", [$key, $e->getMessage()])
                );
            }
        }

        if ($this->validationState->isValidationRequired()) {
            $errors = [];
            if ($configMerger && !$configMerger->validate($this->_schemaFile, $errors)) {
                $message = "Invalid Document \n";
                throw new LocalizedException(
                    new \Magento\Framework\Phrase($message . implode("\n", $errors))
                );
            }
        }

        $output = [];
        if ($configMerger) {
            $output = $this->_converter->convert($configMerger->getDom());
        }

        return $output;
    }

    /**
     * Processing nodes of the document before merging
     *
     * @param string $content
     * @return string
     */
    protected function processingDocument($content)
    {
        $object = new DataObject();
        $document = new \DOMDocument();

        $document->loadXML($content);
        $this->compiler->compile($document->documentElement, $object, $object);

        return $document->saveXML();
    }
}
