<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\ReportXml\Config\Reader;

use Magento\Framework\Config\Reader\Filesystem;
use Magento\Framework\Config\ReaderInterface;

/**
 * A reader of reports configuration.
 *
 * Reads configuration data stored in XML format.
 */
class Xml extends Filesystem implements ReaderInterface
{
    /**
     * {@inheritdoc}
     */
    protected $_idAttributes = [
        '/config/report' => 'name',
        // '/config/query/entity' => 'name',
        // '/config/query/entity/link-entity' => 'name',
        // '/config/query/entity/link-entity' => 'name',

    ];

    /**
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param \Magento\Analytics\ReportXml\Config\Converter\Xml $converter
     * @param \Magento\Analytics\ReportXml\Config\SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        \Magento\Analytics\ReportXml\Config\Converter\Xml $converter,
        \Magento\Analytics\ReportXml\Config\SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        $fileName = 'reports.xml',
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
