<?php
/**
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

/**
 * Legacy tests to find obsolete menu declaration
 */
namespace Magento\Test\Legacy;

class ObsoleteMenuTest extends \PHPUnit_Framework_TestCase
{
    public function testMenuDeclaration()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $menuFile
             */
            function ($menuFile) {
                $menuXml = simplexml_load_file($menuFile);
                $xpath = '/config/menu/*[boolean(./children) or boolean(./title) or boolean(./action)]';
                $this->assertEmpty(
                    $menuXml->xpath($xpath),
                    'Obsolete menu structure detected in file ' . $menuFile . '.'
                );
            },
            \Magento\TestFramework\Utility\Files::init()->getMainConfigFiles()
        );
    }
}
