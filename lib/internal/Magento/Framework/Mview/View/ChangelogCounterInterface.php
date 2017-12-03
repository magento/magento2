<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\View;

/**
 * Interface \Magento\Framework\Mview\View\ChangelogCounterInterface
 *
 */
interface ChangelogCounterInterface
{
    /**
     * Retrieve the count of entity ids in the range [$fromVersionId..$toVersionId]
     *
     * @param $fromVersionId
     * @param $toVersionId
     * @return mixed
     */
    public function getListSize($fromVersionId, $toVersionId);
}
