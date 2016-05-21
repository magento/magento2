<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\AttributeConfiguration\InvalidConfigurationException;
use Magento\Eav\Setup\AttributeConfiguration\Provider\ScopeProvider;

class ScopeProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ScopeProvider
     */
    private $provider;

    protected function setUp()
    {
        $this->provider = new ScopeProvider();
    }

    public function dataProviderInvalidScopes()
    {
        return [
            // although the strings "0", "1" and "2" CAN be converted into valid
            // integer store scopes, we have a strict checking in place
            // which would fail these types of values
            ["0"],
            ["1"],
            ["2"],
            [""],
            ["\0"],
            [" "],
            [4],
            [null],
            [true],
            [new \stdClass()],
            [-1],
            [[]],
        ];
    }

    public function dataProviderValidScopes()
    {
        return [
            [ScopedAttributeInterface::SCOPE_GLOBAL],
            [ScopedAttributeInterface::SCOPE_WEBSITE],
            [ScopedAttributeInterface::SCOPE_STORE],
        ];
    }

    /**
     * @param $invalidScope
     * @dataProvider dataProviderInvalidScopes
     * @expectedException Magento\Eav\Setup\AttributeConfiguration\InvalidConfigurationException
     */
    public function testItThrowsOnInvalidScopes($invalidScope)
    {
        $this->provider->resolve($invalidScope);
    }

    /**
     * @param $validScope
     * @dataProvider dataProviderValidScopes
     */
    public function testItDoesNotThrowOnValidScopes($validScope)
    {
        try {
            $this->provider->resolve($validScope);
        } catch (InvalidConfigurationException $e) {
            $this->fail("It should not fail on scope $validScope");
        }
    }
}
