<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Search\Model;

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
     * @var \Magento\Framework\ObjectManagerInterface
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
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param string $path
     * @param string $scopeType
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
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
     * @return \Magento\Framework\Search\AdapterInterface
     */
    public function create(array $data = [])
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
