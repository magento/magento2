<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaGallery\Model\Directory\Config;

use Magento\Framework\App\Area;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Config\Reader\Filesystem;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Framework\Config\Dom;

class Reader extends Filesystem implements ReaderInterface
{
    /**
     * List of id attributes for merge
     *
     * @var array
     */
    protected $_idAttributes = [
        '/config/patterns' => 'patterns',
        '/config/patterns/pattern' => 'name',
    ];

    /**
     * XML Configuration file name
     */
    private const XML_FILE_NAME = 'directory.xml';

    /**
     * Construct the FileSystem Reader Class
     *
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param Converter $converter
     * @param SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(
        FileResolverInterface $fileResolver,
        Converter $converter,
        SchemaLocator $schemaLocator,
        ValidationStateInterface $validationState,
        $fileName = self::XML_FILE_NAME,
        $idAttributes = [],
        $domDocumentClass = Dom::class,
        $defaultScope = Area::AREA_GLOBAL
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
