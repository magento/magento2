<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module;

use Magento\Framework\App\Resource;
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
     * Creates setup
     *
     * @param Resource $appResource
     * @return Setup
     */
    public function create(Resource $appResource = null)
    {
        $objectManager = $this->objectManagerProvider->get();
        if ($appResource === null) {
            $appResource = $objectManager->get('Magento\Framework\App\Resource');
        }
        return new Setup($appResource);
    }
}
