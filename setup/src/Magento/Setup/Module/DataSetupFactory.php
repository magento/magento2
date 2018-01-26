<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module;

use Magento\Setup\Model\ObjectManagerProvider;

/**
 * Factory class to create DataSetup
 */
class DataSetupFactory
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
     * Creates DataSetup
     *
     * @return DataSetup
     */
    public function create()
    {
        $objectManager = $this->objectManagerProvider->get();
        return new DataSetup($objectManager->get('Magento\Framework\Module\Setup\Context'));
    }
}
