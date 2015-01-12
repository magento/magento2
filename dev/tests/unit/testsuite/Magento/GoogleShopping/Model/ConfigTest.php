<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleShopping\Model;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

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
