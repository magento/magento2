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
namespace Magento\Framework\Code;

class NameBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Code\NameBuilder
     */
    protected $nameBuilder;

    protected function setUp()
    {
        $nelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->nameBuilder = $nelper->getObject('Magento\Framework\Code\NameBuilder');
    }

    /**
     * @param array $parts
     * @param string $expected
     *
     * @dataProvider buildClassNameDataProvider
     */
    public function testBuildClassName($parts, $expected)
    {
        $this->assertEquals($expected, $this->nameBuilder->buildClassName($parts));
    }

    public function buildClassNameDataProvider()
    {
        return [
            [['Checkout', 'Controller', 'Index'], 'Checkout\Controller\Index'],
            [['checkout', 'controller', 'index'], 'Checkout\Controller\Index'],
            [
                ['magento_backend', 'block', 'system', 'store', 'edit'],
                'Magento\Backend\Block\System\Store\Edit'
            ],
            [['MyNamespace', 'MyModule'], 'MyNamespace\MyModule'],
            [['uc', 'words', 'test'], 'Uc\Words\Test'],
            [['ALL', 'CAPS', 'TEST'], 'ALL\CAPS\TEST'],
        ];
    }
}
