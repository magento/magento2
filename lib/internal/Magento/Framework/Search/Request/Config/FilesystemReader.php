<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Config;

/**
 * Class \Magento\Framework\Search\Request\Config\FilesystemReader
 *
 * @since 2.0.0
 */
class FilesystemReader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * List of identifier attributes for merging
     *
     * @var array
     * @since 2.0.0
     */
    protected $_idAttributes = [
        '/requests/request' => 'query',
        '/requests/request/queries/query' => 'name',
        '/requests/request/queries/query/queryReference' => 'ref',
        '/requests/request/queries/query/match' => 'field',
        '/requests/request/queries/query/filterReference' => 'ref',
        '/requests/request/filters/filter' => 'name',
        '/requests/request/filters/filter/filterReference' => 'ref',
        '/requests/request/aggregations/bucket' => 'name',
        '/requests/request/dimensions/dimension' => 'name',
    ];

    /**
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param Converter $converter
     * @param SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        \Magento\Framework\Search\Request\Config\Converter $converter,
        \Magento\Framework\Search\Request\Config\SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        $fileName = 'search_request.xml',
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
