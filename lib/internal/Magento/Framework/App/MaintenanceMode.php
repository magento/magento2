<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use IPTools\IP;
use IPTools\Network;
use IPTools\Range;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Event\Manager;
use Magento\Framework\Filesystem;

/**
 * Application Maintenance Mode
 */
class MaintenanceMode
{
    /**
     * Maintenance flag file name
     *
     * DO NOT consolidate this file and the IP allow list into one.
     * It is going to work much faster in 99% of cases: the isOn() will return false whenever file doesn't exist.
     */
    const FLAG_FILENAME = '.maintenance.flag';

    /**
     * IP-addresses file name
     */
    const IP_FILENAME = '.maintenance.ip';

    /**
     * Maintenance flag dir
     */
    const FLAG_DIR = DirectoryList::VAR_DIR;

    /**
     * Path to store files
     *
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $flagDir;

    /**
     * @var Manager
     */
    private $eventManager;

    /**
     * @param Filesystem $filesystem
     * @param Manager|null $eventManager
     */
    public function __construct(Filesystem $filesystem, ?Manager $eventManager = null)
    {
        $this->flagDir = $filesystem->getDirectoryWrite(self::FLAG_DIR);
        $this->eventManager = $eventManager ?: ObjectManager::getInstance()->get(Manager::class);
    }

    /**
     * Checks whether mode is on
     *
     * Optionally specify an IP-address to compare against the allow list
     *
     * @param string $remoteAddr
     * @return bool
     */
    public function isOn($remoteAddr = '')
    {
        if (!$this->flagDir->isExist(self::FLAG_FILENAME)) {
            return false;
        }
        if ($remoteAddr) {
            $allowedAddresses = $this->getAddressInfo();
            $remoteAddress = new IP($remoteAddr);
            foreach ($allowedAddresses as $allowed) {
                if (Range::parse($allowed)->contains($remoteAddress)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Sets maintenance mode "on" or "off"
     *
     * @param bool $isOn
     * @return bool
     */
    public function set($isOn)
    {
        $this->eventManager->dispatch('maintenance_mode_changed', ['isOn' => $isOn]);

        if ($isOn) {
            return $this->flagDir->touch(self::FLAG_FILENAME);
        }
        if ($this->flagDir->isExist(self::FLAG_FILENAME)) {
            return $this->flagDir->delete(self::FLAG_FILENAME);
        }
        return true;
    }

    /**
     * Sets list of allowed IP addresses
     *
     * @param string $addresses
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function setAddresses($addresses)
    {
        $addresses = (string)$addresses;
        if (empty($addresses)) {
            if ($this->flagDir->isExist(self::IP_FILENAME)) {
                return $this->flagDir->delete(self::IP_FILENAME);
            }
            return true;
        }
        $addresses = $this->normaliseIPAddresses($addresses);
        if (!preg_match('/^[^\s,]+(,[^\s,]+)*$/', $addresses)) {
            throw new \InvalidArgumentException("One or more IP-addresses is expected (comma-separated)\n");
        }
        $result = $this->flagDir->writeFile(self::IP_FILENAME, $addresses);
        return false !== $result;
    }

    /**
     * Get list of IP addresses effective for maintenance mode
     *
     * @return string[]
     */
    public function getAddressInfo()
    {
        if ($this->flagDir->isExist(self::IP_FILENAME)) {
            $temp = $this->flagDir->readFile(self::IP_FILENAME);
            return explode(',', trim($temp));
        } else {
            return [];
        }
    }

    /**
     * Take a string of IP addresses or ranges (comma separated) and return a
     * string of IP ranges with no overlaps nor duplicates (comma separated).
     *
     * @param  string $addresses
     * @return string
     */
    private function normaliseIPAddresses(string $addresses): string
    {
        $addresses = explode(',', $addresses);
        $addresses = array_filter($addresses); // remove empty strings
        $networks = [];
        foreach ($addresses as $address) {
            $networks[] = Network::parse(trim($address));
        }
        $networks = array_unique($networks); // remove obvious duplicates

        // Combine adjacent ranges where feasible
        if (count($networks) > 1) {
            // Sort the list so we can compare each item with the following to
            // determine if they are adjacent.
            sort($networks, SORT_NATURAL);

            // Define end point here as we expect the array to grow within the loop
            $penultimate = count($networks) - 1;
            for ($i = 0; $i < $penultimate; $i++) {
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

        // Remove overlapping entries from the list
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

        return implode(',', $networks);
    }
}
