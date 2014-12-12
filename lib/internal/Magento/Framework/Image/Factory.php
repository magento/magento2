<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Image;

use Magento\Framework\ObjectManagerInterface;

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
    public function __construct(ObjectManagerInterface $objectManager, AdapterFactory $adapterFactory)
    {
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
        return $this->objectManager->create('Magento\Framework\Image', ['adapter' => $adapter, 'fileName' => $fileName]);
    }
}
