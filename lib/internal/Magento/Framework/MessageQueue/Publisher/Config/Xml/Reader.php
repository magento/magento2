<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config\Xml;

use \Magento\Framework\MessageQueue\Publisher\Config\ReaderInterface;

/**
 * Reader for etc/queue_publisher.xml configs.
 */
class Reader extends \Magento\Framework\Config\Reader\Filesystem implements ReaderInterface
{
    /**
     * {@inheritdoc}
     */
    protected $_idAttributes = [
        '/config/publisher' => 'topic',
        '/config/publisher/connection' => 'name'
    ];

    /**
     * {@inheritdoc}
     */
    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        Converter $converter,
        SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        $fileName = 'queue_publisher.xml',
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
