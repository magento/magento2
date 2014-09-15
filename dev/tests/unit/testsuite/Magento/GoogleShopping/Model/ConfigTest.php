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

namespace Magento\GoogleShopping\Model;

use \Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    /**
     * @var \Magento\GoogleShopping\Model\Config
     */
    protected $model;

    public function setUp()
    {
        $this->scopeConfig = $this->getMock(
            'Magento\Framework\App\Config\ScopeConfigInterface',
            ['getValue', 'isSetFlag'],
            [],
            '',
            false
        );
        $this->model = (new ObjectManagerHelper($this))->getObject(
            'Magento\GoogleShopping\Model\Config',
            [
                'scopeConfig' => $this->scopeConfig,
            ]
        );
    }

    public function testGetAccountPassword()
    {
        $storeId = 1;
        $configPasswordKey = 'password';
        $password = 'foopass';
        $this->scopeConfig
            ->expects($this->once())
            ->method('getValue')
            ->with(
                'google/googleshopping/' . $configPasswordKey,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            )->will($this->returnValue($password));
        $this->assertEquals($password, $this->model->getAccountPassword($storeId));
    }
}
