<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module;

use Magento\Framework\App\ResourceConnection;
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
     * @param ResourceConnection $appResource
     * @return Setup
     */
    public function create(ResourceConnection $appResource = null)
    {
        $objectManager = $this->objectManagerProvider->get();
        if ($appResource === null) {
            $appResource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        }
        return new Setup($appResource);
    }
}
