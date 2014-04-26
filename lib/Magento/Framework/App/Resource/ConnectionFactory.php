<?php
/**
 * Connection adapter factory
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\App\Resource;

class ConnectionFactory
{
    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\Arguments
     */
    protected $_localConfig;

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param \Magento\Framework\App\Arguments $localConfig
     */
    public function __construct(
        \Magento\Framework\ObjectManager $objectManager,
        \Magento\Framework\App\Arguments $localConfig
    ) {
        $this->_objectManager = $objectManager;
        $this->_localConfig = $localConfig;
    }

    /**
     * Create connection adapter instance
     *
     * @param string $connectionName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @throws \InvalidArgumentException
     */
    public function create($connectionName)
    {
        $connectionConfig = $this->_localConfig->getConnection($connectionName);
        if (!$connectionConfig || !isset($connectionConfig['active']) || !$connectionConfig['active']) {
            return null;
        }

        if (!isset($connectionConfig['adapter'])) {
            throw new \InvalidArgumentException('Adapter is not set for connection "' . $connectionName . '"');
        }

        $adapterInstance = $this->_objectManager->create($connectionConfig['adapter'], $connectionConfig);

        if (!$adapterInstance instanceof ConnectionAdapterInterface) {
            throw new \InvalidArgumentException('Trying to create wrong connection adapter');
        }

        return $adapterInstance->getConnection();
    }
}
