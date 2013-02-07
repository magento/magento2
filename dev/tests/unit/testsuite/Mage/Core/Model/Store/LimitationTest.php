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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_Store_LimitationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param string $totalCount
     * @param string $configuredCount
     * @param bool $expected
     * @dataProvider canCreateDataProvider
     */
    public function testCanCreate($totalCount, $configuredCount, $expected)
    {
        $resource = $this->getMock('Mage_Core_Model_Resource_Store', array('countAll'), array(), '', false);
        if ($totalCount) {
            $resource->expects($this->once())->method('countAll')->will($this->returnValue($totalCount));
        }
        $config = $this->getMock('Mage_Core_Model_Config', array('getNode'), array(), '', false);
        $config->expects($this->any())->method('getNode')
            ->with('global/functional_limitation/max_store_count')
            ->will($this->returnValue($configuredCount));
        $model = new Mage_Core_Model_Store_Limitation($resource, $config);
        $this->assertEquals($expected, $model->canCreate());

        // verify that resource model is invoked only when needed (see expectation "once" above)
        new Mage_Core_Model_Store_Limitation($resource, $config);
    }

    /**
     * @return array
     */
    public function canCreateDataProvider()
    {
        return array(
            'no limit'       => array(0, '', true),
            'negative limit' => array(2, -1, false),
            'zero limit'     => array(2, 0, false),
            'limit < count'  => array(2, 1, false),
            'limit = count'  => array(2, 2, false),
            'limit > count'  => array(2, 3, true),
        );
    }
}
