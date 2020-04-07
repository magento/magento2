<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model\Content\Config;

use Magento\Framework\App\Area;
use Magento\Framework\Config\Dom;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\Reader\Filesystem;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Config\ValidationStateInterface;

/**
 * Media content config reader
 */
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
    private const XML_FILE_NAME = 'media_content.xml';

    /**
     * @param FileResolverInterface $fileResolver
     * @param Converter $converter
     * @param SchemaLocator $schemaLocator
     * @param ValidationStateInterface $validationState
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
