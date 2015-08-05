<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Amqp\Config;

/**
 * AMQP configuration filesystem loader. Loads all publisher configuration from XML file
 */
class Reader extends \Magento\Framework\Config\Reader\Filesystem
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
        '/config/bind' => ['queue', 'exchange', 'topic']
    ];

    /**
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param \Magento\Framework\Amqp\Config\Converter $converter
     * @param \Magento\Framework\Amqp\Config\SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        \Magento\Framework\Amqp\Config\Converter $converter,
        \Magento\Framework\Amqp\Config\SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        $fileName = 'queue.xml',
        $idAttributes = [],
        $domDocumentClass = 'Magento\Framework\Config\Dom',
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
