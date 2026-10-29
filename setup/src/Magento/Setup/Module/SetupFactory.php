<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Setup\Module;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\ObjectManagerProvider;

/**
 * Factory class to create Setup
 *
 * @api
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
    public function create(?ResourceConnection $appResource = null)
    {
        $objectManager = $this->objectManagerProvider->get();
        if ($appResource === null) {
            $appResource = $objectManager->get(\Magento\Framework\App\ResourceConnection::class);
        }
        return new Setup($appResource);
    }
}
