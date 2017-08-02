<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\ObjectManagerProvider;

/**
 * Factory class to create Setup
 *
 * @api
 * @since 2.0.0
 */
class SetupFactory
{
    /**
     * @var ObjectManagerProvider
     * @since 2.0.0
     */
    private $objectManagerProvider;

    /**
     * Constructor
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function create(ResourceConnection $appResource = null)
    {
        $objectManager = $this->objectManagerProvider->get();
        if ($appResource === null) {
            $appResource = $objectManager->get(\Magento\Framework\App\ResourceConnection::class);
        }
        return new Setup($appResource);
    }
}
