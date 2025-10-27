<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Model;

/**
 * Interface to switch indexer mode
 *
 * @api
 */
interface ModeSwitcherInterface
{
    /**
     * Returns data object that contains dimension modes
     *
     * @return DimensionModes
     */
    public function getDimensionModes(): DimensionModes;

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
