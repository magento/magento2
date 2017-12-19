<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\Cookie\Prolongation;

/**
 * Cookie prolongation interfaces.
 */
interface ProlongationInterface
{
    /**
     * Prolongs cookie.
     *
     * @return void
     */
    public function execute();
}