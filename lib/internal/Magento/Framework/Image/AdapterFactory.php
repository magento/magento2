<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Image;

use Exception;
use InvalidArgumentException;
use Magento\Framework\Image\Adapter\AdapterInterface;
use Magento\Framework\Image\Adapter\ConfigInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for Adapters that Image Library is using to process images
 *
 * @api
 */
class AdapterFactory
{
    /**
     * @var Adapter\ConfigInterface
     */
    private $config;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $adapterMap;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Adapter\ConfigInterface $config
     * @param array $adapterMap
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ConfigInterface $config,
        array $adapterMap = []
    ) {
        $this->objectManager = $objectManager;
        $this->config = $config;
        $this->adapterMap = $adapterMap;
    }

    /**
     * Return specified image adapter
     *
     * @param string $adapterAlias
     * @return AdapterInterface
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function create($adapterAlias = null)
    {
        $this->adapterMap = array_merge($this->config->getAdapters(), $this->adapterMap);
        $adapterAlias = !empty($adapterAlias) ? $adapterAlias : $this->config->getAdapterAlias();
        if (empty($adapterAlias)) {
            throw new InvalidArgumentException('Image adapter is not selected.');
        }
        if (empty($this->adapterMap[$adapterAlias]['class'])) {
            throw new InvalidArgumentException("Image adapter for '{$adapterAlias}' is not setup.");
        }
        $imageAdapter = $this->objectManager->create($this->adapterMap[$adapterAlias]['class']);
        if (!$imageAdapter instanceof Adapter\AdapterInterface) {
            throw new InvalidArgumentException(
                $this->adapterMap[$adapterAlias]['class'] .
                ' is not instance of \Magento\Framework\Image\Adapter\AdapterInterface'
            );
        }
        $imageAdapter->checkDependencies();
        return $imageAdapter;
    }
}
