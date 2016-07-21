<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology\Config\Xml;

use \Magento\Framework\MessageQueue\Topology\Config\ReaderInterface;

/**
 * Reader for etc/queue_topology.xml configs.
 */
class Reader extends \Magento\Framework\Config\Reader\Filesystem implements ReaderInterface
{
    /**
     * {@inheritdoc}
     */
    protected $_idAttributes = [
        '/config/topology' => 'topic',
        '/config/topology/connection' => 'name'
    ];

    /**
     * {@inheritdoc}
     */
    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        Converter $converter,
        SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        $fileName = 'queue_topology.xml',
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
