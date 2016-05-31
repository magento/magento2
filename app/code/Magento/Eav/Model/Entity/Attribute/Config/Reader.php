<?php
/**
 * Attribute configuration reader
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute\Config;

class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * Xml merging attributes
     *
     * @var array
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
