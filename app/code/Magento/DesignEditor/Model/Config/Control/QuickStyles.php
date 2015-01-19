<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Model\Config\Control;

use Magento\Framework\Module\Dir\Reader;

/**
 * Quick styles configuration
 */
class QuickStyles extends \Magento\DesignEditor\Model\Config\Control\AbstractControl
{
    /**
     * Keys of layout params attributes
     *
     * @var string[]
     */
    protected $_controlAttributes = ['title', 'tab', 'column'];

    /**
     * Module configuration file reader
     *
     * @var Reader
     */
    protected $_moduleReader;

    /**
     * Constructor
     *
     * @param array $configFiles
     * @param Reader $moduleReader
     */
    public function __construct($configFiles, Reader $moduleReader)
    {
        $this->_moduleReader = $moduleReader;
        parent::__construct($configFiles);
    }

    /**
     * Path to quick_styles.xsd
     *
     * @return string
     */
    public function getSchemaFile()
    {
        return $this->_moduleReader->getModuleDir('etc', 'Magento_DesignEditor') . '/quick_styles.xsd';
    }
}
