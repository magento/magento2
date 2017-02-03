<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Render;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\RenderInterface;

/**
 * Class RenderFactory
 */
class RenderFactory
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get method
     *
     * @param string $type
     * @return RenderInterface
     * @throws \InvalidArgumentException
     */
    public function get($type)
    {
        $className = 'Magento\\Framework\\View\\Render\\' . ucfirst($type);
        $model = $this->objectManager->get($className);
        if (!$model instanceof RenderInterface) {
            throw new \InvalidArgumentException(
                'Type "' . $type . '" is not instance on Magento\Framework\View\RenderInterface'
            );
        }
        return $model;
    }
}
