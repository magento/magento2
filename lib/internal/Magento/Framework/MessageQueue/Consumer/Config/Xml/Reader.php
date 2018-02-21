<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config\Xml;

use Magento\Framework\MessageQueue\Consumer\Config\ReaderInterface;

/**
 * Reader for etc/queue_consumer.xml configs.
 */
class Reader extends \Magento\Framework\Config\Reader\Filesystem implements ReaderInterface
{
    /**
     * {@inheritdoc}
     */
    protected $_idAttributes = [
        '/config/consumer' => 'name'
    ];

    /**
     * {@inheritdoc}
     */
    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        \Magento\Framework\MessageQueue\Consumer\Config\Xml\Converter $converter,
        \Magento\Framework\MessageQueue\Consumer\Config\Xml\SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        $fileName = 'queue_consumer.xml',
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
