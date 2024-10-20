<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Model\ServiceConfig;

use Magento\Framework\Config\Dom;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\Reader\Filesystem;
use Magento\Framework\Config\ValidationStateInterface;

/**
 * Service config data reader.
 */
class Reader extends Filesystem
{
    /**
     * List of id attributes for merge
     *
     * @var array
     */
    protected $_idAttributes = [
        '/services/service' => ['class', 'method'],
        '/services/route'   => ['url', 'method']
    ];

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
        $fileName = 'webapi_async.xml',
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
}
