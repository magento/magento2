<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Ui\DataProvider\Config;

use Magento\Framework\Config\ValidationStateInterface;

/**
 * Class Reader
 */
class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * List of id attributes for merge
     *
     * @var array
     */
    protected $_idAttributes = ['/config/dataSource' => 'name'];

    /**
     * @param FileResolver $fileResolver
     * @param Converter $converter
     * @param SchemaLocator $schemaLocator
     * @param ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(
        FileResolver $fileResolver,
        Converter $converter,
        SchemaLocator $schemaLocator,
        ValidationStateInterface $validationState,
        $fileName = '*.xml',
        $idAttributes = [],
        $domDocumentClass = 'Magento\Framework\Config\Dom',
        $defaultScope = ''
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
