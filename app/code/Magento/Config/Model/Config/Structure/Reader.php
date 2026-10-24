<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Config\Model\Config\Structure;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface;

/**
 * Reads and merges system.xml configuration files describing the admin configuration structure.
 *
 * @api
 * @since 100.0.2
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
                    new \Magento\Framework\Phrase(
                        'The XML in file "%1" is invalid:' . "\n%2\nVerify the XML and try again.",
                        [$key, $e->getMessage()]
                    )
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
     * @throws \Magento\Framework\Config\Dom\ValidationException
     * @return string
     */
    protected function processingDocument($content)
    {
        $object = new DataObject();
        $document = new \DOMDocument();
        $useInternalErrors = libxml_use_internal_errors(true);
        try {
            $isLoaded = $document->loadXML($content);
            $errors = libxml_get_errors();
        } catch (\Exception $e) {
            libxml_clear_errors();
            libxml_use_internal_errors($useInternalErrors);
            throw new \Magento\Framework\Config\Dom\ValidationException($e->getMessage());
        }
        libxml_clear_errors();
        libxml_use_internal_errors($useInternalErrors);
        if (!$isLoaded) {
            $message = $errors ? trim($errors[0]->message) : 'Invalid XML content.';
            throw new \Magento\Framework\Config\Dom\ValidationException($message);
        }
        $this->compiler->compile($document->documentElement, $object, $object);

        return $document->saveXML();
    }
}
