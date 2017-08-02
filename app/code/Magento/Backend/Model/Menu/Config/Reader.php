<?php
/**
 * Menu configuration files handler
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Menu\Config;

/**
 * @api
 * @since 2.0.0
 */
class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param \Magento\Backend\Model\Menu\Config\Converter $converter
     * @param \Magento\Backend\Model\Menu\Config\SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        \Magento\Backend\Model\Menu\Config\Converter $converter,
        \Magento\Backend\Model\Menu\Config\SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        $fileName = 'menu.xml',
        $idAttributes = [],
        $domDocumentClass = \Magento\Backend\Model\Menu\Config\Menu\Dom::class,
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
