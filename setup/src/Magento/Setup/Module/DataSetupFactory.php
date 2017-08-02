<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module;

use Magento\Setup\Model\ObjectManagerProvider;

/**
 * Factory class to create DataSetup
 * @api
 * @since 2.0.0
 */
class DataSetupFactory
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
     * Creates DataSetup
     *
     * @return DataSetup
     * @since 2.0.0
     */
    public function create()
    {
        $objectManager = $this->objectManagerProvider->get();
        return new DataSetup($objectManager->get(\Magento\Framework\Module\Setup\Context::class));
    }
}
