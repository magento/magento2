<?php
/**
 * Attribute configuration reader
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
