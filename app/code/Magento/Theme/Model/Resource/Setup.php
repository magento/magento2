<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Resource;
use Magento\Framework\Setup\ModuleDataResourceInterface;

/**
 * Core resource setup
 */
class Setup extends \Magento\Framework\Module\DataSetup
{
    /**
     * @var \Magento\Theme\Model\Resource\Theme\CollectionFactory
     */
    protected $_themeResourceFactory;

    /**
     * @var \Magento\Theme\Model\Theme\CollectionFactory
     */
    protected $_themeFactory;

    /**
     * @param \Magento\Framework\Module\Setup\Context $context
     * @param string $resourceName
     * @param \Magento\Theme\Model\Resource\Theme\CollectionFactory $themeResourceFactory
     * @param \Magento\Theme\Model\Theme\CollectionFactory $themeFactory
     * @param string $moduleName
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Module\Setup\Context $context,
        $resourceName,
        \Magento\Theme\Model\Resource\Theme\CollectionFactory $themeResourceFactory,
        \Magento\Theme\Model\Theme\CollectionFactory $themeFactory,
        $moduleName = 'Magento_Core',
        $connectionName = ModuleDataResourceInterface::DEFAULT_SETUP_CONNECTION
    ) {
        $this->_themeResourceFactory = $themeResourceFactory;
        $this->_themeFactory = $themeFactory;
        parent::__construct($context, $resourceName, $moduleName, $connectionName);
    }

    /**
     * @return \Magento\Theme\Model\Resource\Theme\Collection
     */
    public function createThemeResourceFactory()
    {
        return $this->_themeResourceFactory->create();
    }

    /**
     * @return \Magento\Theme\Model\Theme\Collection
     */
    public function createThemeFactory()
    {
        return $this->_themeFactory->create();
    }
}
