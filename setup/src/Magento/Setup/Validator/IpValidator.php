<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Validator;

use IPTools\Network;
use IPTools\Range;

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
     * Normalise / canonicalise list of IP addresses / ranges
     *
     * @param  string[] $addresses
     * @return string[]
     */
    public function normaliseIPAddresses(array &$addresses): array
    {
        $addresses = array_filter($addresses); // remove empty strings

        $networks = [];
        foreach ($addresses as $address) {
            $networks[] = Network::parse(trim($address));
        }

        $networks = array_unique($networks); // remove obvious duplicates

        do {
            $startCount = count($networks);
            $this->combineAdjacentNetworks($networks);
            $this->removeOverlappingNetworks($networks);
            $endCount = count($networks);
        } while ($startCount != $endCount);

        $networks = array_map(function ($network) {
            return (string) $network;
        }, $networks);

        return $networks;
    }

    /**
     * Filter ips into 'none', valid and invalid ips
     *
     * @param string[] $ips
     * @return void
     */
    private function filterIps(array $ips)
    {
        foreach ($ips as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                $this->validIps[] = $ip;
            } elseif ($ip == 'none') {
                $this->none[] = $ip;
            } else {
                try {
                    $network = Network::parse($ip);
                    $this->validIps[] = (string) $network;
                } catch (\Exception $e) {
                    // Network::parse will throw an \Exception on error
                    $this->invalidIps[] = $ip;
                }
            }
        }
    }

    /**
     * Combine adjacent networks
     *
     * @param string[] $networks
     * @return void
     */
    private function combineAdjacentNetworks(array &$networks): void
    {
        if (count($networks) > 1) {
            // Sort the list so we can compare each item with the following to
            // determine if they are adjacent.
            sort($networks, SORT_NATURAL);

            // Define end point here as we expect the array to grow within the loop
            $penultimate = count($networks) - 2;
            for ($i = 0; $i <= $penultimate; $i++) {
                $a = $networks[$i];
                $b = $networks[$i + 1];
                if ($a->getLastIP()->next() == $b->getFirstIP()) {
                    $range = new Range($a->getFirstIP(), $b->getLastIP());
                    foreach ($range->getNetworks() as $network) {
                        $networks[] = $network;
                    }
                }
            }
        }
    }

    /**
     * Remove Overlapping Networks
     *
     * @param string[] $networks
     * @return void
     */
    private function removeOverlappingNetworks(array &$networks): void
    {
        if (count($networks) > 1) {
            // Sort the list so we can compare each item with the following to
            // determine if the former includes the latter.
            sort($networks, SORT_NATURAL);

            $lastRange = null;
            $networks = array_filter($networks, function ($network) use (&$lastRange) {
                if ($lastRange && $lastRange->contains($network)) {
                    return false;
                }
                $lastRange = Range::parse($network);
                return true;
            });
        }
    }
}
