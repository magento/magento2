<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Model\Indexer;

/**
 * Interface to switch indexer mode
 */
interface ModeSwitcherInterface
{
    /**
     * Returns array of possible dimensions modes
     * Example:
     * [
     *    'mode1' => ['dimension1'],
     *    'mode2' => ['dimension2']
     *    'mode3' => ['dimension1', 'dimension2']
     * ]
     *
     * @return array
     */
    public function getDimensionSwitchModes(): array;

    /**
     * Switch dimension mode
     *
     * @param string $currentMode
     * @param string $previousMode
     * @throws \InvalidArgumentException
     * @throws \Zend_Db_Exception
     * @return void
     */
    public function switchMode(string $currentMode, string $previousMode);
}
