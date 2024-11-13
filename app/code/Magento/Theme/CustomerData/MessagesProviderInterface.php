<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\CustomerData;

use Magento\Framework\Message\Collection;

interface MessagesProviderInterface
{
    /**
     *  Get the messages stored in session before session clear
     *
     * @return Collection
     */
    public function getMessages(): Collection;
}
