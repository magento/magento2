<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wonderland\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use Magento\Wonderland\Api\Data\FakeExtensibleTwoInterface;

class FakeExtensibleTwo extends AbstractExtensibleObject implements FakeExtensibleTwoInterface
{
    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }
}
