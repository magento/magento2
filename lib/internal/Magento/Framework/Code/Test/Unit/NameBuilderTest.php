<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Test\Unit;

class NameBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Code\NameBuilder
     */
    protected $nameBuilder;

    protected function setUp()
    {
        $nelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->nameBuilder = $nelper->getObject(\Magento\Framework\Code\NameBuilder::class);
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

    /**
     * @return array
     */
    public function buildClassNameDataProvider()
    {
        return [
            [['Checkout', 'Controller', 'Index'], 'Checkout\Controller\Index'],
            [['checkout', 'controller', 'index'], 'Checkout\Controller\Index'],
            [
                ['magento_backend', 'block', 'system', 'store', 'edit'], \Magento\Backend\Block\System\Store\Edit::class
            ],
            [['MyNamespace', 'MyModule'], 'MyNamespace\MyModule'],
            [['uc', 'words', 'test'], 'Uc\Words\Test'],
            [['ALL', 'CAPS', 'TEST'], 'ALL\CAPS\TEST'],
        ];
    }
}
