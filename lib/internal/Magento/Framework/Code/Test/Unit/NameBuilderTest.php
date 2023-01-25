<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Test\Unit;

use PHPUnit\Framework\TestCase;
use Magento\Framework\Code\NameBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\Block\System\Store\Edit;

class NameBuilderTest extends TestCase
{
    /**
     * @var NameBuilder
     */
    protected $nameBuilder;

    protected function setUp(): void
    {
        $nelper = new ObjectManager($this);
        $this->nameBuilder = $nelper->getObject(NameBuilder::class);
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
                ['magento_backend', 'block', 'system', 'store', 'edit'], Edit::class
            ],
            [['MyNamespace', 'MyModule'], 'MyNamespace\MyModule'],
            [['uc', 'words', 'test'], 'Uc\Words\Test'],
            [['ALL', 'CAPS', 'TEST'], 'ALL\CAPS\TEST'],
        ];
    }
}
