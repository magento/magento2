<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

/**
 * Service that allows to handle a flag object as a scalar value.
 */
class FlagManager
{
    /**
     * @var \Magento\Framework\FlagManager
     */
    private $flagManager;

    /**
     * @param \Magento\Framework\FlagManager $flagManager
     */
    public function __construct(
        \Magento\Framework\FlagManager $flagManager
    ) {
        $this->flagManager = $flagManager;
    }

    /**
     * Return raw data from flag
     * @param string $flagCode
     * @return mixed
     */
    public function getFlagData($flagCode)
    {
        return $this->flagManager->getFlagData($flagCode);
    }

    /**
     * Save flag by code
     * @param string $flagCode
     * @param mixed $value
     * @return bool
     */
    public function saveFlag($flagCode, $value)
    {
        return $this->flagManager->saveFlag($flagCode, $value);
    }

    /**
     * Delete flag by code
     *
     * @param string $flagCode
     * @return bool
     */
    public function deleteFlag($flagCode)
    {
        return $this->flagManager->deleteFlag($flagCode);
    }
}
