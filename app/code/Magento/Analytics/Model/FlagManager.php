<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\FlagFactory;
use Magento\Framework\Flag\FlagResource;
use Magento\Framework\Flag;

/**
 * Class FlagManager
 */
class FlagManager
{
    /**
     * @var FlagFactory
     */
    private $flagFactory;

    /**
     * @var FlagResource
     */
    private $flagResource;

    public function __construct(
        FlagFactory $flagFactory,
        FlagResource $flagResource
    ) {
        $this->flagFactory = $flagFactory;
        $this->flagResource = $flagResource;
    }

    /**
     * Return raw data from flag
     * @param string $flagCode
     * @return mixed|null
     */
    public function getFlagData($flagCode)
    {
        return $this->getFlagObject($flagCode)->getFlagData();
    }

    /**
     * Update flag by code
     * @param string $flagCode
     * @return void
     */
    public function updateFlag($flagCode, $value)
    {
        $flag = $this->getFlagObject($flagCode);
        $flag->setFlagData($value);
        $this->flagResource->save($flag);
    }

    /**
     * Delete flag by code
     * @param string $flagCode
     * @return void
     */
    public function deleteFlag($flagCode)
    {
        $flag = $this->getFlagObject($flagCode);
        $this->flagResource->delete($flag);
    }

    /**
     * @param string $flagCode
     * @return Flag
     */
    private function getFlagObject($flagCode)
    {
        /** @var Flag $flag */
        $flag = $this->flagFactory
            ->create(['data' => ['flag_code' => $flagCode]]);
        $this->flagResource->load($flag, $flagCode, 'flag_code');
        return $flag;
    }
}
