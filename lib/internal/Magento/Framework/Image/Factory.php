<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Image;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class \Magento\Framework\Image\Factory
 *
 * @since 2.0.0
 */
class Factory
{
    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @var AdapterFactory
     * @since 2.0.0
     */
    protected $adapterFactory;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param AdapterFactory $adapterFactory
     * @since 2.0.0
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        AdapterFactory $adapterFactory
    ) {
        $this->objectManager = $objectManager;
        $this->adapterFactory = $adapterFactory;
    }

    /**
     * Create instance of \Magento\Framework\Image
     *
     * @param string|null $fileName
     * @param string|null $adapterName
     * @return \Magento\Framework\Image
     * @since 2.0.0
     */
    public function create($fileName = null, $adapterName = null)
    {
        $adapter = $this->adapterFactory->create($adapterName);
        return $this->objectManager->create(
            \Magento\Framework\Image::class,
            ['adapter' => $adapter, 'fileName' => $fileName]
        );
    }
}
