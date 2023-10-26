<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mview\View\ChangelogBatchWalker;

use Magento\Framework\DB\Select;
use Magento\Framework\Mview\View\ChangelogInterface;

/**
 * Interface \Magento\Framework\Mview\View\ChangelogBatchWalker\IdsSelectBuilderInterface
 *
 */
interface IdsSelectBuilderInterface
{
    /**
     * Build SQL query to collect unique ids of changed entries from changelog table
     *
     * @param \Magento\Framework\Mview\View\ChangelogInterface $changelog
     * @return \Magento\Framework\DB\Select
     */
    public function build(ChangelogInterface $changelog): Select;
}
