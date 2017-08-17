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
 */
class Factory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var AdapterFactory
     */
    protected $adapterFactory;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param AdapterFactory $adapterFactory
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
