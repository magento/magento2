<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Utility;

use IPTools\Network;
use IPTools\Range;

/**
 * Utility class to normalise list of IP addresses
 */
class IPAddressNormaliser
{
    /**
     * Normalise / canonicalise list of IP addresses / ranges
     *
     * @param  string[] $addresses
     * @return string[]
     */
    public function execute(array $addresses): array
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
