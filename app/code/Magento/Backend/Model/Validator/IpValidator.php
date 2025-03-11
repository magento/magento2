<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Model\Validator;

use Magento\Framework\App\Utility\IPAddress;

/**
 * Class to validate list of IPs for maintenance commands
 */
class IpValidator
{
    /**
     * @var string[]
     */
    private $none;

    /**
     * @var string[]
     */
    private $validIps;

    /**
     * @var string[]
     */
    private $invalidIps;

    /**
     * @param IPAddress $ipAddress
     */
    public function __construct(
        private readonly IPAddress $ipAddress,
    ) {
    }

    /**
     * Validates list of ips
     *
     * @param string[] $ips
     * @param bool $noneAllowed
     *
     * @return string[]
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validateIps(array $ips, $noneAllowed)
    {
        $this->none = [];
        $this->validIps = [];
        $this->invalidIps = [];
        $messages = [];

        $this->filterIps($ips);

        if (count($this->none) > 0 && !$noneAllowed) {
            $messages[] = "'none' is not allowed";
        } elseif ($noneAllowed && count($this->none) > 1) {
            $messages[] = "'none' can be only used once";
        } elseif ($noneAllowed && count($this->none) > 0 &&
            (count($this->validIps) > 0 || count($this->invalidIps) > 0)
        ) {
            $messages[] = "Multiple values are not allowed when 'none' is used";
        } else {
            foreach ($this->invalidIps as $invalidIp) {
                $messages[] = "Invalid IP $invalidIp";
            }
        }

        return $messages;
    }

    /**
     * Filter ips into 'none', valid and invalid ips
     *
     * @param string[] $ips
     *
     * @return void
     */
    private function filterIps(array $ips)
    {
        foreach ($ips as $ip) {
            if ($ip === 'none') {
                $this->none[] = $ip;
            } elseif ($this->ipAddress->isValidAddress($ip)) {
                $this->validIps[] = $ip;
            } elseif ($this->ipAddress->isValidRange($ip)) {
                $this->validIps[] = $ip;
            } else {
                $this->invalidIps[] = $ip;
            }
        }
    }
}
