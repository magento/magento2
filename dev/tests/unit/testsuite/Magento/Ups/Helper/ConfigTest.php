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
namespace Magento\Ups\Helper;

/**
 * Config helper Test
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Ups config helper
     *
     * @var \Magento\Ups\Helper\Config
     */
    protected $helper;

    public function setUp()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->helper = $objectManagerHelper->getObject('Magento\Ups\Helper\Config');
    }

    /**
     * @param mixed $result
     * @param null|string $type
     * @param string $code
     * @dataProvider getCodeDataProvider
     */
    public function testGetData($result, $type = null, $code = null)
    {
        $this->assertEquals($result, $this->helper->getCode($type, $code));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function getCodeDataProvider()
    {
        return array(
            array(false),
            array(false, 'not-exist-type'),
            array(false, 'not-exist-type', 'not-exist-code'),
            array(false, 'action'),
            array(array('single' => '3', 'all' => '4'), 'action', ''),
            array('3', 'action', 'single')
        );
    }
}
