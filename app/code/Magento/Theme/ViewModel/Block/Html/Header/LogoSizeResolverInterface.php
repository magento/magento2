<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\ViewModel\Block\Html\Header;

/**
 * Interface for resolving logo size
 */
interface LogoSizeResolverInterface
{
    /**
     * Return configured logo width
     *
     * @param int|null $storeId
     * @return null|int
     */
    public function getWidth(?int $storeId = null): ?int;

    /**
     * Return configured logo height
     *
     * @param int|null $storeId
     * @return null|int
     */
    public function getHeight(?int $storeId = null): ?int;
}
