<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Communication\Config\Reader;

use Magento\Framework\Config\Dom;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\ValidationStateInterface;

/**
 * Communication configuration filesystem reader. Reads data from XML configs.
 */
class XmlReader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * List of id attributes for merge
     *
     * @var array
     */
    protected $_idAttributes = [
        '/config/topic' => 'name',
        '/config/topic/handler' => 'name'
    ];

    /**
     * @param FileResolverInterface $fileResolver
     * @param XmlReader\Converter $converter
     * @param XmlReader\SchemaLocator $schemaLocator
     * @param ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     */
    public function __construct(
        FileResolverInterface $fileResolver,
        XmlReader\Converter $converter,
        XmlReader\SchemaLocator $schemaLocator,
        ValidationStateInterface $validationState,
        $fileName = 'communication.xml',
        $idAttributes = [],
        $domDocumentClass = Dom::class,
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
    //phpcs:enable Generic.CodeAnalysis.UselessOverridingMethod
}
