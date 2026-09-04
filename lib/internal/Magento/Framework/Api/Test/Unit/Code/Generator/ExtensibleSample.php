<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\Test\Unit\Code\Generator;

use Magento\Framework\Model\AbstractExtensibleModel;

class ExtensibleSample extends AbstractExtensibleModel implements
    ExtensibleSampleInterface
{
    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $this->getData('items');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        $this->getData('name');
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        $this->getData('count');
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        $this->getData('created_at');
    }
}
