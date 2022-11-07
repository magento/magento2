<?php

namespace Magento\AsyncConfig\Model\Entity;

use Magento\AsyncConfig\Api\Data\AsyncConfigMessageInterface;

class AsyncConfigMessage implements AsyncConfigMessageInterface
{
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
