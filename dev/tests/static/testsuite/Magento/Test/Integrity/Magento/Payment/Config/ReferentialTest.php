<?php
/**
 * Validates that payment groups referenced from store configuration matches the groups declared in payment.xml
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Integrity\Magento\Payment\Config;

class ReferentialTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string[] $usedGroups all payment groups used in store configuration
     */
    protected static $_usedGroups = array();

    /** @var string[] $_registeredGroups all registered payment groups */
    protected static $_registeredGroups = array();

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
        $configFiles = \Magento\TestFramework\Utility\Files::init()->getConfigFiles('config.xml', array(), false);
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
        $configFiles = \Magento\TestFramework\Utility\Files::init()->getConfigFiles('payment.xml', array(), false);
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
