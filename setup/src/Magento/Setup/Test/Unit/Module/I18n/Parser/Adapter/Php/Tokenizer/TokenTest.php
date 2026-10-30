<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\I18n\Parser\Adapter\Php\Tokenizer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\Token;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @covers \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\Token
 */
class TokenTest extends TestCase
{
    /**
     * @var ObjectManager
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
     */
    #[DataProvider('isNewDataProvider')]
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
     */
    #[DataProvider('isNamespaceSeparatorDataProvider')]
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
     */
    #[DataProvider('isIdentifierDataProvider')]
    public function testIsIdentifier($name, $value, $result)
    {
        $token = $this->createToken($name, $value);
        $this->assertEquals($result, $token->isIdentifier());
    }

    /**
     * @return array
     */
    public static function isNewDataProvider()
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
    public static function isNamespaceSeparatorDataProvider()
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
    public static function isIdentifierDataProvider()
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
            Token::class,
            [
                'name' => $name,
                'value' => $value,
                'line' => $line
            ]
        );
    }

    public function testIsConcatenateOperatorTrue()
    {
        $token = new Token('.', '.');
        $this->assertTrue($token->isConcatenateOperator());
    }

    public function testIsConcatenateOperatorFalse()
    {
        $token = new Token(',', ',');
        $this->assertFalse($token->isConcatenateOperator());
    }
}
