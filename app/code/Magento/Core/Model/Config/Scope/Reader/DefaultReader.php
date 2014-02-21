<?php
/**
 * Default configuration reader
 *
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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Config\Scope\Reader;

class DefaultReader implements \Magento\App\Config\Scope\ReaderInterface
{
    /**
     * @var \Magento\App\Config\Initial
     */
    protected $_initialConfig;

    /**
     * @var \Magento\App\Config\Scope\Converter
     */
    protected $_converter;

    /**
     * @var \Magento\Core\Model\Resource\Config\Value\Collection\ScopedFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @param \Magento\App\Config\Initial $initialConfig
     * @param \Magento\App\Config\Scope\Converter $converter
     * @param \Magento\Core\Model\Resource\Config\Value\Collection\ScopedFactory $collectionFactory
     * @param \Magento\App\State $appState
     */
    public function __construct(
        \Magento\App\Config\Initial $initialConfig,
        \Magento\App\Config\Scope\Converter $converter,
        \Magento\Core\Model\Resource\Config\Value\Collection\ScopedFactory $collectionFactory,
        \Magento\App\State $appState
    ) {
        $this->_initialConfig = $initialConfig;
        $this->_converter = $converter;
        $this->_collectionFactory = $collectionFactory;
        $this->_appState = $appState;
    }

    /**
     * Read configuration data
     *
     * @return array
     */
    public function read()
    {
        $config = $this->_initialConfig->getData(\Magento\BaseScopeInterface::SCOPE_DEFAULT);
        if ($this->_appState->isInstalled()) {
            $collection = $this->_collectionFactory->create(
                array('scope' => \Magento\BaseScopeInterface::SCOPE_DEFAULT)
            );
            $dbDefaultConfig = array();
            foreach ($collection as $item) {
                $dbDefaultConfig[$item->getPath()] = $item->getValue();
            }
            $dbDefaultConfig = $this->_converter->convert($dbDefaultConfig);
            $config = array_replace_recursive($config, $dbDefaultConfig);
        }
        return $config;
    }
}
