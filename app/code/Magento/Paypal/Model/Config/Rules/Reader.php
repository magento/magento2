<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Config\Rules;

use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Config\ValidationStateInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Config\Reader\Filesystem;
use Magento\Paypal\Helper\Backend;

/**
 * Class Reader
 */
class Reader extends Filesystem
{
    /**
     * List of identifier attributes for merging
     *
     * @var array
     */
    protected $_idAttributes = [
        '/rules/payment' => 'id',
        '/rules/payment(/relation)+' => 'target'
    ];

    /**
     * Constructor
     *
     * @param FileResolverInterface $fileResolver
     * @param Converter $converter
     * @param SchemaLocatorInterface $schemaLocator
     * @param ValidationStateInterface $validationState
     * @param Backend $helper
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(
        FileResolverInterface $fileResolver,
        Converter $converter,
        SchemaLocatorInterface $schemaLocator,
        ValidationStateInterface $validationState,
        Backend $helper,
        $fileName = 'adminhtml/rules/payment_{country}.xml',
        $idAttributes = [],
        $domDocumentClass = 'Magento\Framework\Config\Dom',
        $defaultScope = 'primary'
    ) {
        $fileName = str_replace('{country}', strtolower($helper->getConfigurationCountryCode()), $fileName);
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
     * Load configuration scope
     *
     * @param string|null $scope
     * @return array
     */
    public function read($scope = null)
    {
        $scope = $scope ?: $this->_defaultScope;
        $fileList = $this->_fileResolver->get($this->_fileName, $scope);

        if (!count($fileList)) {
            return $this->_readFiles($this->_fileResolver->get('adminhtml/rules/payment_other.xml', $scope));
        }

        return $this->_readFiles($fileList);
    }
}
