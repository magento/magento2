<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Menu\Config;

/**
 * @api
 * @since 100.0.2
 */
class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param \Magento\Backend\Model\Menu\Config\Converter $converter
     * @param \Magento\Backend\Model\Menu\Config\SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        \Magento\Backend\Model\Menu\Config\Converter $converter,
        \Magento\Backend\Model\Menu\Config\SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        $fileName = 'menu.xml',
        $idAttributes = [],
        $domDocumentClass = \Magento\Backend\Model\Menu\Config\Menu\Dom::class,
        $defaultScope = 'global'
    ) {
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
}
