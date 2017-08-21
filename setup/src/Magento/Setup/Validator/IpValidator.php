<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Validator;

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
     * Validates list of ips
     *
     * @param string[] $ips
     * @param bool $noneAllowed
     * @return string[]
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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
     */
    private function filterIps(array $ips)
    {
        foreach ($ips as $range) {
            if ($range === 'none') {
                $this->none[] = $range;
                continue;
            }

            $cidr = 32;
            $ip = $range;
            if (strpos($range, '/') !== false) {
                list($ip, $cidr) = explode('/', $range);
            }

            $ipValidator = new \Zend_Validate_Ip(['allowipv6' => true, 'allowipv4' => true]);
            if (!$ipValidator->isValid($ip)) {
                $this->invalidIps[] = $range;
                continue;
            }

            $ipv4Validator = new \Zend_Validate_Ip(['allowipv6' => false, 'allowipv4' => true]);
            $max = $ipv4Validator->isValid($ip) ? 32 : 128;
            if ($cidr < 0 || $cidr > $max) {
                $this->invalidIps[] = $range;
                continue;
            }

            $this->validIps[] = $range;
        }
    }
}
