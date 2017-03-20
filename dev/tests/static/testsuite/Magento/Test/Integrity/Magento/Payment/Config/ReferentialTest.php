<?php
/**
 * Validates that payment groups referenced from store configuration matches the groups declared in payment.xml
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Magento\Payment\Config;

class ReferentialTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string[] $usedGroups all payment groups used in store configuration
     */
    protected static $_usedGroups = [];

    /** @var string[] $_registeredGroups all registered payment groups */
    protected static $_registeredGroups = [];

    public static function setUpBeforeClass()
    {
        self::_populateUsedGroups();
        self::_populateRegisteredGroups();
    }

    /**
     * Gathers all payment groups used in store configuration
     */
    private static function _populateUsedGroups()
    {
        /**
         * @var string[] $configFiles
         */
        $configFiles = \Magento\Framework\App\Utility\Files::init()->getConfigFiles('config.xml', [], false);
        /**
         * @var string $file
         */
        foreach ($configFiles as $file) {
            /**
             * @var \DOMDocument $dom
             */
            $dom = new \DOMDocument();
            $dom->loadXML(file_get_contents($file));

            $xpath = new \DOMXPath($dom);
            foreach ($xpath->query('/config/*/payment/*/group') as $group) {
                if (!in_array($group->nodeValue, self::$_usedGroups)) {
                    self::$_usedGroups[] = $group->nodeValue;
                }
            }
        }
    }

    /**
     * Gathers all registered payment groups
     */
    private static function _populateRegisteredGroups()
    {
        /**
         * @var string[] $configFiles
         */
        $configFiles = \Magento\Framework\App\Utility\Files::init()->getConfigFiles('payment.xml', [], false);
        /**
         * @var string $file
         */
        foreach ($configFiles as $file) {
            /**
             * @var \DOMDocument $dom
             */
            $dom = new \DOMDocument();
            $dom->loadXML(file_get_contents($file));

            $xpath = new \DOMXPath($dom);
            foreach ($xpath->query('/payment/groups/group') as $group) {
                $id = $group->attributes->getNamedItem('id')->nodeValue;
                if (!in_array($id, self::$_registeredGroups)) {
                    self::$_registeredGroups[] = $id;
                }
            }
        }
    }

    public function testGroupsExists()
    {
        $missing = array_diff(self::$_usedGroups, self::$_registeredGroups);

        if (!empty($missing)) {
            $message = sprintf(
                "The groups, referenced in store configuration for the payment, " .
                "don't correspond to any payment group declared in payment.xml: %s",
                implode(', ', $missing)
            );
            $this->fail($message);
        }
    }
}
