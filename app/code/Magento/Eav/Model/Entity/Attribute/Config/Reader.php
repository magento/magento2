<?php
/**
 * Attribute configuration reader
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute\Config;

/**
 * Class \Magento\Eav\Model\Entity\Attribute\Config\Reader
 *
 * @since 2.0.0
 */
class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * Xml merging attributes
     *
     * @var array
     * @since 2.0.0
     */
    protected $_idAttributes = [
        '/config/entity' => 'type',
        '/config/entity/attribute' => 'code',
        '/config/entity/attribute/field' => 'code',
    ];

    /**
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param \Magento\Eav\Model\Entity\Attribute\Config\Converter $converter
     * @param \Magento\Eav\Model\Entity\Attribute\Config\SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        \Magento\Eav\Model\Entity\Attribute\Config\Converter $converter,
        \Magento\Eav\Model\Entity\Attribute\Config\SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState
    ) {
        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            'eav_attributes.xml',
            []
        );
    }
}
