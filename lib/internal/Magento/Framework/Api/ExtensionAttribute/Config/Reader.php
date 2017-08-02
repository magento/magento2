<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\ExtensionAttribute\Config;

/**
 * Class \Magento\Framework\Api\ExtensionAttribute\Config\Reader
 *
 * @since 2.0.0
 */
class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * List of id attributes for merge
     *
     * @var array
     * @since 2.0.0
     */
    protected $_idAttributes = [
        '/config/extension_attributes' => 'for',
        '/config/extension_attributes/attribute' => 'code',
    ];

    /**
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param \Magento\Framework\Api\ExtensionAttribute\Config\Converter $converter
     * @param \Magento\Framework\Api\ExtensionAttribute\Config\SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        \Magento\Framework\Api\ExtensionAttribute\Config\Converter $converter,
        \Magento\Framework\Api\ExtensionAttribute\Config\SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        $fileName = 'extension_attributes.xml',
        $idAttributes = [],
        $domDocumentClass = \Magento\Framework\Config\Dom::class,
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
