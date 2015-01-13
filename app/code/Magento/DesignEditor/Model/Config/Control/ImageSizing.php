<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Model\Config\Control;

/**
 * Image Sizing configuration
 */
class ImageSizing extends \Magento\DesignEditor\Model\Config\Control\AbstractControl
{
    /**
     * Keys of layout params attributes
     *
     * @var string[]
     */
    protected $_controlAttributes = ['title'];

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $_moduleReader;

    /**
     * @param array $configFiles
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     */
    public function __construct($configFiles, \Magento\Framework\Module\Dir\Reader $moduleReader)
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
        return $this->_moduleReader->getModuleDir('etc', 'Magento_DesignEditor') . '/image_sizing.xsd';
    }
}
