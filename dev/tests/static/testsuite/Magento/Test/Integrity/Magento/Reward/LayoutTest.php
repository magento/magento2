<?php
/**
 * Validator of class names in Reward nodes
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Test\Integrity\Magento\Reward;

class LayoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $file
     * @dataProvider layoutFileDataProvider
     */
    public function testInitRewardTypeClasses($file)
    {
        $xml = simplexml_load_file($file);
        $nodes = $xml->xpath('//argument[@name="reward_type"]') ? : array();
        $errors = array();
        /** @var \SimpleXMLElement $node */
        foreach ($nodes as $node) {
            $class = (string)$node;
            if (!\Magento\TestFramework\Utility\Files::init()->classFileExists($class, $path)) {
                $errors[] = "'{$class}' => '{$path}'";
            }
        }
        if ($errors) {
            $this->fail("Invalid class declarations in {$file}. Files are not found in code pools:\n"
                . implode(PHP_EOL, $errors) . PHP_EOL
            );
        }
    }

    /**
     * @return array
     */
    public function layoutFileDataProvider()
    {
        return \Magento\TestFramework\Utility\Files::init()->getLayoutFiles();
    }
}
