<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\FileSystem;

use Magento\Framework\Config\ReaderInterface;

/**
 * Class Reader
 */
class Reader extends \Magento\Framework\Config\Reader\Filesystem implements ReaderInterface
{
    /**
     * Attributes by names of which we will do nodes merge
     *
     * @var array
     */
    private $idAttributes = [
        '/schema/table' => 'name',
        '/schema/table/column' => 'name',
        '/schema/table/constraint' => 'name',
        '/schema/table/index' => 'name',
        '/schema/table/index/column' => 'name',
        '/schema/table/constraint/column' => 'name',
    ];

    /**
     * Reader constructor.
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param \Magento\Setup\Model\Declaration\Schema\Xml\Converter $converter
     * @param \Magento\Setup\Model\Declaration\Schema\Xml\SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param string $fileName
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        \Magento\Setup\Model\Declaration\Schema\Xml\Converter $converter,
        \Magento\Setup\Model\Declaration\Schema\Xml\SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        $fileName = 'db_schema.xml',
        $domDocumentClass = \Magento\Framework\Config\Dom::class,
        $defaultScope = 'global'
    ) {
        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            $fileName,
            $this->idAttributes,
            $domDocumentClass,
            $defaultScope
        );
    }
}
