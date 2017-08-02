<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Config\Reader;

/**
 * Class \Magento\Framework\ObjectManager\Config\Reader\Dom
 *
 * @since 2.0.0
 */
class Dom extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * Name of an attribute that stands for data type of node values
     */
    const TYPE_ATTRIBUTE = 'xsi:type';

    /**
     * @var array
     * @since 2.0.0
     */
    protected $_idAttributes = [
        '/config/preference' => 'for',
        '/config/(type|virtualType)' => 'name',
        '/config/(type|virtualType)/plugin' => 'name',
        '/config/(type|virtualType)/arguments/argument' => 'name',
        '/config/(type|virtualType)/arguments/argument(/item)+' => 'name',
    ];

    /**
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param \Magento\Framework\ObjectManager\Config\Mapper\Dom $converter
     * @param \Magento\Framework\ObjectManager\Config\SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        \Magento\Framework\ObjectManager\Config\Mapper\Dom $converter,
        \Magento\Framework\ObjectManager\Config\SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        $fileName = 'di.xml',
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

    /**
     * Create and return a config merger instance that takes into account types of arguments
     *
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function _createConfigMerger($mergerClass, $initialContents)
    {
        return new $mergerClass(
            $initialContents,
            $this->validationState,
            $this->_idAttributes,
            self::TYPE_ATTRIBUTE,
            $this->_perFileSchema
        );
    }
}
