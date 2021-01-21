<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n\Parser\Adapter\Php\Tokenizer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\Token;

/**
 * @covers \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\Token
 */
class TokenTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @covers \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\Token::isNew
     *
     * @param int $name
     * @param string $value
     * @param bool $result
     * @dataProvider testIsNewDataProvider
     */
    public function testIsNew($name, $value, $result)
    {
        $token = $this->createToken($name, $value);
        $this->assertEquals($result, $token->isNew());
    }

    /**
     * @covers \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\Token::isNamespaceSeparator
     *
     * @param int $name
     * @param string $value
     * @param bool $result
     * @dataProvider testIsNamespaceSeparatorDataProvider
     */
    public function testIsNamespaceSeparator($name, $value, $result)
    {
        $token = $this->createToken($name, $value);
        $this->assertEquals($result, $token->isNamespaceSeparator());
    }

    /**
     * @covers \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\Token::isIdentifier
     *
     * @param int $name
     * @param string $value
     * @param bool $result
     * @dataProvider testIsIdentifierDataProvider
     */
    public function testIsIdentifier($name, $value, $result)
    {
        $token = $this->createToken($name, $value);
        $this->assertEquals($result, $token->isIdentifier());
    }

    /**
     * @return array
     */
    public function testIsNewDataProvider()
    {
        return [
            'new' => ['name' => T_NEW, 'value' => 'new', 'result' => true],
            'namespace' => ['name' => T_NS_SEPARATOR, 'value' => '\\', 'result' => false],
            'identifier' => ['name' => T_STRING, 'value' => '__', 'result' => false]
        ];
    }

    /**
     * @return array
     */
    public function testIsNamespaceSeparatorDataProvider()
    {
        return [
            'new' => ['name' => T_NEW, 'value' => 'new', 'result' => false],
            'namespace' => ['name' => T_NS_SEPARATOR, 'value' => '\\', 'result' => true],
            'identifier' => ['name' => T_STRING, 'value' => '__', 'result' => false]
        ];
    }

    /**
     * @return array
     */
    public function testIsIdentifierDataProvider()
    {
        return [
            'new' => ['name' => T_NEW, 'value' => 'new', 'result' => false],
            'namespace' => ['name' => T_NS_SEPARATOR, 'value' => '\\', 'result' => false],
            'identifier' => ['name' => T_STRING, 'value' => '__', 'result' => true]
        ];
    }

    /**
     * @param int $name
     * @param string $value
     * @return Token
     */
    protected function createToken($name, $value)
    {
        $line = 110;
        return $this->objectManager->getObject(
            \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\Token::class,
            [
                'name' => $name,
                'value' => $value,
                'line' => $line
            ]
        );
    }

    public function testIsConcatenateOperatorTrue()
    {
        $token = new \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\Token('.', '.');
        $this->assertTrue($token->isConcatenateOperator());
    }

    public function testIsConcatenateOperatorFalse()
    {
        $token = new \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\Token(',', ',');
        $this->assertFalse($token->isConcatenateOperator());
    }
}
