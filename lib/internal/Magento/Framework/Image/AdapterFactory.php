<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Image;

class AdapterFactory
{
    /**
     * @var Adapter\ConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $adapterMap;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param Adapter\ConfigInterface $config
     * @param array $adapterMap
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Image\Adapter\ConfigInterface $config,
        array $adapterMap = []
    ) {
        $this->objectManager = $objectManager;
        $this->config = $config;
        $this->adapterMap = array_merge($config->getAdapters(), $adapterMap);
    }

    /**
     * Return specified image adapter
     *
     * @param string $adapterAlias
     * @return \Magento\Framework\Image\Adapter\AdapterInterface
     * @throws \InvalidArgumentException
     */
    public function create($adapterAlias = null)
    {
        $adapterAlias = !empty($adapterAlias) ? $adapterAlias : $this->config->getAdapterAlias();
        if (empty($adapterAlias)) {
            throw new \InvalidArgumentException('Image adapter is not selected.');
        }
        if (empty($this->adapterMap[$adapterAlias]['class'])) {
            throw new \InvalidArgumentException("Image adapter for '{$adapterAlias}' is not setup.");
        }
        $imageAdapter = $this->objectManager->create($this->adapterMap[$adapterAlias]['class']);
        if (!$imageAdapter instanceof Adapter\AdapterInterface) {
            throw new \InvalidArgumentException(
                $this->adapterMap[$adapterAlias]['class'] .
                ' is not instance of \Magento\Framework\Image\Adapter\AdapterInterface'
            );
        }
        $imageAdapter->checkDependencies();
        return $imageAdapter;
    }
}
