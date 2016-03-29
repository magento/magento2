<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\Module\Status;

/**
 * Class ModuleStatusFactory creates instance of Status
 */
class ModuleStatusFactory
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
     * Creates Status object
     *
     * @return Status
     */
    public function create()
    {
        return $this->objectManagerProvider->get()->get('Magento\Framework\Module\Status');
    }
}
