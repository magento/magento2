<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsyncConfig\Model\Entity;

use Magento\AsyncConfig\Api\Data\AsyncConfigMessageInterface;

class AsyncConfigMessage implements AsyncConfigMessageInterface
{
    /**
     * @var string
     */
    private $data;

    /**
     * @inheritDoc
     */
    public function getConfigData()
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function setConfigData($data)
    {
        $this->data = $data;
    }
}
