<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\FlagFactory;
use Magento\Framework\Flag\FlagResource;
use Magento\Framework\Flag;

/**
 * Service that allows to handle a flag object as a scalar value.
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

    /**
     * FlagManager constructor.
     * @param FlagFactory $flagFactory
     * @param FlagResource $flagResource
     */
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
     * @return mixed
     */
    public function getFlagData($flagCode)
    {
        return $this->getFlagObject($flagCode)->getFlagData();
    }

    /**
     * Save flag by code
     * @param string $flagCode
     * @param mixed $value
     * @return bool
     */
    public function saveFlag($flagCode, $value)
    {
        $flag = $this->getFlagObject($flagCode);
        $flag->setFlagData($value);
        $this->flagResource->save($flag);
        return true;
    }

    /**
     * Delete flag by code
     * @param string $flagCode
     * @return bool
     */
    public function deleteFlag($flagCode)
    {
        $flag = $this->getFlagObject($flagCode);
        if ($flag->getId()) {
            $this->flagResource->delete($flag);
        }
        return true;
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
