<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider\Config;

use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Module\Dir\Reader as DirReader;

/**
 * Class SchemaLocator
 */
class SchemaLocator implements SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for both individual and merged configs
     *
     * @var string
     */
    private $_schema;

    /**
     * @param DirReader $moduleReader
     */
    public function __construct(DirReader $moduleReader)
    {
        $this->_schema = $moduleReader->getModuleDir('etc', 'Magento_Ui') . '/data_source.xsd';
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * {@inheritdoc}
     */
    public function getPerFileSchema()
    {
        return $this->_schema;
    }
}
