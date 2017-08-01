<?php
/**
 * Fieldset configuration reader
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DataObject\Copy\Config;

/**
 * Class \Magento\Framework\DataObject\Copy\Config\Reader
 *
 * @since 2.0.0
 */
class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * List of identifier attributes for merging
     *
     * @var array
     * @since 2.0.0
     */
    protected $_idAttributes = [
        '/config/scope' => 'id',
        '/config/scope/fieldset' => 'id',
        '/config/scope/fieldset/field' => 'name',
        '/config/scope/fieldset/field/aspect' => 'name',
    ];

    /**
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param \Magento\Framework\DataObject\Copy\Config\Converter $converter
     * @param \Magento\Framework\Config\SchemaLocatorInterface $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        \Magento\Framework\DataObject\Copy\Config\Converter $converter,
        \Magento\Framework\Config\SchemaLocatorInterface $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        $fileName = 'fieldset.xml',
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
