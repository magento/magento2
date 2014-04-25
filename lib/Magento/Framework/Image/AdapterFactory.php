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
namespace Magento\Framework\Image;

class AdapterFactory
{
    /**
     * @var Adapter\ConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $adapterMap;

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param Adapter\ConfigInterface $config
     * @param array $adapterMap
     */
    public function __construct(
        \Magento\Framework\ObjectManager $objectManager,
        \Magento\Framework\Image\Adapter\ConfigInterface $config,
        array $adapterMap = array()
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
