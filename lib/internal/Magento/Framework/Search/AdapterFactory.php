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
namespace Magento\Framework\Search;

/**
 * Adapter Factory
 */
class AdapterFactory
{
    /**
     * Scope configuration
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    /**
     * Config path
     *
     * @var string
     */
    protected $path;

    /**
     * Config Scope
     */
    protected $scope;

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param string $path
     * @param string $scopeType
     */
    public function __construct(
        \Magento\Framework\ObjectManager $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $path,
        $scopeType
    ) {
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->path = $path;
        $this->scope = $scopeType;
    }

    /**
     * Create Adapter instance
     *
     * @param array $data
     * @return AdapterInterface
     */
    public function create(array $data = array())
    {
        $adapterClass = $this->scopeConfig->getValue($this->path, $this->scope);
        $adapter = $this->objectManager->create($adapterClass, $data);
        if (!($adapter instanceof \Magento\Framework\Search\AdapterInterface)) {
            throw new \InvalidArgumentException(
                'Adapter must implement \Magento\Framework\Search\AdapterInterface'
            );
        }
        return $adapter;
    }
}
