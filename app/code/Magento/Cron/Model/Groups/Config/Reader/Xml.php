<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\Groups\Config\Reader;

/**
 * Reader for XML files
 * @since 2.0.0
 */
class Xml extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * Mapping XML name nodes
     *
     * @var array
     * @since 2.0.0
     */
    protected $_idAttributes = ['/config/group' => 'id'];

    /**
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param \Magento\Cron\Model\Groups\Config\Converter\Xml $converter
     * @param \Magento\Cron\Model\Groups\Config\SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        \Magento\Cron\Model\Groups\Config\Converter\Xml $converter,
        \Magento\Cron\Model\Groups\Config\SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        $fileName = 'cron_groups.xml',
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
