<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\Fixture;

use Magento\Framework\Model\AbstractExtensibleModel;

class DataObject extends AbstractExtensibleModel implements DataObjectInterface
{
    /**
     * @inheritDoc
     */
    public function setFirstASecond(string $value): void
    {
        $this->setData('first_a_second', $value);
    }

    /**
     * @inheritDoc
     */
    public function setFirstAtSecond(string $value): void
    {
        $this->setData('first_at_second', $value);
    }

    /**
     * @inheritDoc
     */
    public function setFirstATMSecond(string$value): void
    {
        $this->setData('first_a_t_m_second', $value);
    }
}
