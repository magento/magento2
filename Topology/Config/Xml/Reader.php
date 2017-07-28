<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology\Config\Xml;

use \Magento\Framework\MessageQueue\Topology\Config\ReaderInterface;

/**
 * Reader for etc/queue_topology.xml configs.
 * @since 2.2.0
 */
class Reader extends \Magento\Framework\Config\Reader\Filesystem implements ReaderInterface
{
    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    protected $_idAttributes = [
        '/config/exchange' => ['name', 'connection'],
        '/config/exchange/arguments/argument' => 'name',
        '/config/exchange/arguments/argument(/item)+' => 'name',
        '/config/exchange/binding' => 'id',
        '/config/exchange/binding/arguments/argument' => 'name',
        '/config/exchange/binding/arguments/argument(/item)+' => 'name',
    ];

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        Converter $converter,
        SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        $fileName = 'queue_topology.xml',
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
