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
 * Test class for \Magento\Weee\Model\Config
 */
namespace Magento\Weee\Model;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the methods that rely on the ScopeConfigInterface object to provide their return values
     *
     * @param string $method
     * @param string $path
     * @param bool $configValue
     * @param bool $expectedValue
     * @dataProvider dataProviderScopeConfigMethods
     */
    public function testScopeConfigMethods($method, $path, $configValue, $expectedValue)
    {
        $scopeConfigMock = $this->getMockForAbstractClass('Magento\Framework\App\Config\ScopeConfigInterface');
        $scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null)
            ->will($this->returnValue($configValue));
        $scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, null)
            ->will($this->returnValue($configValue));

        $taxData = $this->getMock('Magento\Tax\Helper\Data', [], [], '', false);

        /** @var \Magento\Weee\Model\Config */
        $model = new Config($scopeConfigMock, $taxData);
        $this->assertEquals($expectedValue, $model->{$method}());
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function dataProviderScopeConfigMethods()
    {
        return [
            [
                'getPriceDisplayType',
                Config::XML_PATH_FPT_DISPLAY_PRODUCT_VIEW,
                true,
                true
            ],
            [
                'getListPriceDisplayType',
                Config::XML_PATH_FPT_DISPLAY_PRODUCT_LIST,
                true,
                true
            ],
            [
                'getSalesPriceDisplayType',
                Config::XML_PATH_FPT_DISPLAY_SALES,
                true,
                true
            ],
            [
                'getEmailPriceDisplayType',
                Config::XML_PATH_FPT_DISPLAY_EMAIL,
                true,
                true
            ],
            [
                'includeInSubtotal',
                Config::XML_PATH_FPT_INCLUDE_IN_SUBTOTAL,
                true,
                true
            ],
            [
                'isDiscounted',
                Config::XML_PATH_FPT_DISCOUNTED,
                true,
                true
            ],
            [
                'isTaxable',
                Config::XML_PATH_FPT_TAXABLE,
                true,
                true
            ],
            [
                'isEnabled',
                Config::XML_PATH_FPT_ENABLED,
                true,
                true
            ]
        ];
    }
}
