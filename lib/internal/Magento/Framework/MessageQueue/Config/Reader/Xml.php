<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Config\Reader;

/**
 * MessageQueue configuration filesystem loader. Loads all publisher configuration from XML file
 *
 * @deprecated 103.0.0
 */
class Xml extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * List of id attributes for merge
     *
     * @var array
     */
    protected $_idAttributes = [
        '/config/publisher' => 'name',
        '/config/consumer' => 'name',
        '/config/topic' => 'name',
        '/config/bind' => ['queue', 'exchange', 'topic'],
        '/config/broker' => 'topic',
        '/config/broker/consumer' => 'name'
    ];

    /**
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param \Magento\Framework\MessageQueue\Config\Reader\Xml\CompositeConverter $converter
     * @param \Magento\Framework\MessageQueue\Config\Reader\Xml\SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        \Magento\Framework\MessageQueue\Config\Reader\Xml\CompositeConverter $converter,
        \Magento\Framework\MessageQueue\Config\Reader\Xml\SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        $fileName = 'queue.xml',
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
