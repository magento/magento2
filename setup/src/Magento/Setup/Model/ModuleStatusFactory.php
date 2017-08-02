<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\Module\Status;

/**
 * Class ModuleStatusFactory creates instance of Status
 * @since 2.0.0
 */
class ModuleStatusFactory
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
     * Creates Status object
     *
     * @return Status
     * @since 2.0.0
     */
    public function create()
    {
        return $this->objectManagerProvider->get()->get(\Magento\Framework\Module\Status::class);
    }
}
