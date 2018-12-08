<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

namespace Magento\Store\Model;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreSwitcher\CannotSwitchStoreException;

/**
 * Handles store switching procedure and detects url for final redirect after store switching.
 */
interface StoreSwitcherInterface
{
    /**
     * @param StoreInterface $fromStore store where we came from
     * @param StoreInterface $targetStore store where to go to
     * @param string $redirectUrl original url requested for redirect after switching
     * @return string url to be redirected after switching
     * @throws CannotSwitchStoreException
     */
    public function switch(StoreInterface $fromStore, StoreInterface $targetStore, string $redirectUrl): string;
}
