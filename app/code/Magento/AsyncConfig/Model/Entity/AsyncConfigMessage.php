<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
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
