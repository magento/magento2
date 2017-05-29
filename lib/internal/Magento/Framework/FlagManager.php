<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

use Magento\Framework\Flag\FlagResource;

/**
 * Service that allows to handle a flag object as a scalar value.
 */
class FlagManager
{
    /**
     * The factory of flags.
     *
     * @var FlagFactory
     * @see Flag
     */
    private $flagFactory;

    /**
     * The flag resource.
     *
     * @var FlagResource
     */
    private $flagResource;

    /**
     *
     * @param FlagFactory $flagFactory The factory of flags
     * @param FlagResource $flagResource The flag resource
     */
    public function __construct(
        FlagFactory $flagFactory,
        FlagResource $flagResource
    ) {
        $this->flagFactory = $flagFactory;
        $this->flagResource = $flagResource;
    }

    /**
     * Retrieves raw data from the flag.
     *
     * @param string $code The code of flag
     * @return array|string|int
     */
    public function getFlagData($code)
    {
        return $this->getFlagObject($code)->getFlagData();
    }

    /**
     * Saves the flag value by code.
     *
     * @param string $code The code of flag
     * @param array|string|int $value The value of flag
     * @return bool
     */
    public function saveFlag($code, $value)
    {
        $flag = $this->getFlagObject($code);
        $flag->setFlagData($value);
        $this->flagResource->save($flag);

        return true;
    }

    /**
     * Deletes the flag by code.
     *
     * @param string $code The code of flag
     * @return bool
     */
    public function deleteFlag($code)
    {
        $flag = $this->getFlagObject($code);

        if ($flag->getId()) {
            $this->flagResource->delete($flag);
        }

        return true;
    }

    /**
     * Returns flag object
     *
     * @param string $code
     * @return Flag
     */
    private function getFlagObject($code)
    {
        /** @var Flag $flag */
        $flag = $this->flagFactory->create(['data' => ['flag_code' => $code]]);
        $this->flagResource->load(
            $flag,
            $code,
            'flag_code'
        );

        return $flag;
    }
}
