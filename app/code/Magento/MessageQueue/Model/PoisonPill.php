<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Model;

use Magento\MessageQueue\Api\Data\PoisonPillInterface;

/**
 * PoisonPill data class
 */
class PoisonPill extends \Magento\Framework\Model\AbstractModel implements PoisonPillInterface
{
    /**
     * @inheritdoc
     */
    public function getVersion(): ?int
    {
        return $this->_getData('version');
    }

    /**
     * @inheritdoc
     */
    public function setVersion(int $version)
    {
        $this->setData('version', $version);
    }
}
