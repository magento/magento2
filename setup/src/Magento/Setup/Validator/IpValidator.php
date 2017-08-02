<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Validator;

/**
 * Class to validate list of IPs for maintenance commands
 * @since 2.0.0
 */
class IpValidator
{
    /**
     * @var string[]
     * @since 2.0.0
     */
    private $none;

    /**
     * @var string[]
     * @since 2.0.0
     */
    private $validIps;

    /**
     * @var string[]
     * @since 2.0.0
     */
    private $invalidIps;

    /**
     * Validates list of ips
     *
     * @param string[] $ips
     * @param bool $noneAllowed
     * @return string[]
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    public function validateIps(array $ips, $noneAllowed)
    {
        $this->none = [];
        $this->validIps = [];
        $this->invalidIps = [];
        $messages = [];

        $this->filterIps($ips);

        if (sizeof($this->none) > 0 && !$noneAllowed) {
            $messages[] = "'none' is not allowed";
        } elseif ($noneAllowed && sizeof($this->none) > 1) {
            $messages[] = "'none' can be only used once";
        } elseif ($noneAllowed && sizeof($this->none) > 0 &&
            (sizeof($this->validIps) > 0 || sizeof($this->invalidIps) > 0)
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
     * @return void
     * @since 2.0.0
     */
    private function filterIps(array $ips)
    {
        foreach ($ips as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                $this->validIps[] = $ip;
            } elseif ($ip == 'none') {
                $this->none[] = $ip;
            } else {
                $this->invalidIps[] = $ip;
            }
        }
    }
}
