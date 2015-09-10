<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module;

use Magento\Setup\Model\ObjectManagerProvider;

/**
 * Factory class to create Setup
 */
class SetupFactory
{
    /**
     * @var ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * Constructor
     *
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(ObjectManagerProvider $objectManagerProvider)
    {
        $this->objectManagerProvider = $objectManagerProvider;
    }

    /**
     * Creates Setup
     *
     * @return Setup
     */
    public function create()
    {
        $objectManager = $this->objectManagerProvider->get();
        return new Setup($objectManager->get('Magento\Framework\App\Resource'));
    }
}
