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
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Translate\Inline;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testIsActive()
    {
        $store = 'some store';
        $result = 'result';
        $coreStoreConfig = $this->getMock('Magento\Core\Model\Store\Config', array(), array(), '', false);
        $coreStoreConfig
            ->expects($this->once())
            ->method('getConfigFlag')
            ->with($this->equalTo('dev/translate_inline/active'), $this->equalTo($store))
            ->will($this->returnValue($result));
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $config = $objectManager->getObject(
            '\Magento\Core\Model\Translate\Inline\Config',
            array(
                'coreStoreConfig' => $coreStoreConfig
            )
        );
        $this->assertEquals($result, $config->isActive($store));
    }
}
