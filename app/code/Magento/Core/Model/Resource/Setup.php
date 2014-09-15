<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Resource;

/**
 * Core resource setup
 */
class Setup extends \Magento\Framework\Module\Setup
{
    /**
     * @var \Magento\Core\Model\Resource\Theme\CollectionFactory
     */
    protected $_themeResourceFactory;

    /**
     * @var \Magento\Core\Model\Theme\CollectionFactory
     */
    protected $_themeFactory;

    /**
     * @param \Magento\Framework\Module\Setup\Context $context
     * @param string $resourceName
     * @param \Magento\Core\Model\Resource\Theme\CollectionFactory $themeResourceFactory
     * @param \Magento\Core\Model\Theme\CollectionFactory $themeFactory
     * @param string $moduleName
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Module\Setup\Context $context,
        $resourceName,
        \Magento\Core\Model\Resource\Theme\CollectionFactory $themeResourceFactory,
        \Magento\Core\Model\Theme\CollectionFactory $themeFactory,
        $moduleName = 'Magento_Core',
        $connectionName = \Magento\Framework\Module\Updater\SetupInterface::DEFAULT_SETUP_CONNECTION
    ) {
        $this->_themeResourceFactory = $themeResourceFactory;
        $this->_themeFactory = $themeFactory;
        parent::__construct($context, $resourceName, $moduleName, $connectionName);
    }

    /**
     * @return \Magento\Core\Model\Resource\Theme\Collection
     */
    public function createThemeResourceFactory()
    {
        return $this->_themeResourceFactory->create();
    }

    /**
     * @return \Magento\Core\Model\Theme\Collection
     */
    public function createThemeFactory()
    {
        return $this->_themeFactory->create();
    }
}
