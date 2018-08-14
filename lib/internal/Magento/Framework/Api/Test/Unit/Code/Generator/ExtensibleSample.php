<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api\Test\Unit\Code\Generator;

use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Class ExtensibleSample
 */
class ExtensibleSample extends AbstractExtensibleModel implements
    \Magento\Framework\Api\Test\Unit\Code\Generator\ExtensibleSampleInterface
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
