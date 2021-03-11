<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n\Parser\Adapter\Php\Tokenizer;

use Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector;
use Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\Token;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer;

/**
 * @covers \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector
 */
class PhraseCollectorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PhraseCollector
     */
    protected $phraseCollector;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var Tokenizer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $tokenizerMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->tokenizerMock = $this->getMockBuilder(\Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->phraseCollector = $this->objectManager->getObject(
            \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector::class,
            [
                'tokenizer' => $this->tokenizerMock
            ]
        );
    }

    /**
     * @covers \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector::parse
     *
     * @param string $file
     * @param array $isEndOfLoopReturnValues
     * @param array $getNextRealTokenReturnValues
     * @param array $getFunctionArgumentsTokensReturnValues
     * @param array $isMatchingClassReturnValues
     * @param array $result
     * @dataProvider testParseDataProvider
     */
    public function testParse(
        $file,
        array $isEndOfLoopReturnValues,
        array $getNextRealTokenReturnValues,
        array $getFunctionArgumentsTokensReturnValues,
        array $isMatchingClassReturnValues,
        array $result
    ) {
        $matchingClass = 'Phrase';

        $this->tokenizerMock->expects($this->once())
            ->method('parse')
            ->with($file);
        $this->tokenizerMock->expects($this->atLeastOnce())
            ->method('isEndOfLoop')
            ->will(call_user_func_array(
                [$this, 'onConsecutiveCalls'],
                $isEndOfLoopReturnValues
            ));
        $this->tokenizerMock->expects($this->any())
            ->method('getNextRealToken')
            ->will(call_user_func_array(
                [$this, 'onConsecutiveCalls'],
                $getNextRealTokenReturnValues
            ));
        $this->tokenizerMock->expects($this->any())
            ->method('getFunctionArgumentsTokens')
            ->will(call_user_func_array(
                [$this, 'onConsecutiveCalls'],
                $getFunctionArgumentsTokensReturnValues
            ));
        $this->tokenizerMock->expects($this->any())
            ->method('isMatchingClass')
            ->with($matchingClass)
            ->will(call_user_func_array(
                [$this, 'onConsecutiveCalls'],
                $isMatchingClassReturnValues
            ));

        $this->phraseCollector->setIncludeObjects();
        $this->phraseCollector->parse($file);
        $this->assertEquals($result, $this->phraseCollector->getPhrases());
    }

    /**
     * @return array
     */
    public function testParseDataProvider()
    {
        $file = 'path/to/file.php';
        $line = 110;
        return [
            /* Test simulates parsing of the following code:
             *
             * $phrase1 = new \Magento\Framework\Phrase('Testing');
             * $phrase2 = __('More testing');
             */
            'two phrases' => [
                'file' => $file,
                'isEndOfLoopReturnValues' => [
                    false, //before $phrase1
                    false, //at $phrase1
                    false, //at =
                    false, //at new
                    false, //at ;
                    false, //at $phrase2
                    false, //at =
                    false, //at __
                    false, //at ;
                    true //after ;
                ],
                'getNextRealTokenReturnValues' => [
                    $this->createToken(false, false, false, false, '$phrase1'),
                    $this->createToken(false, false, false, false, '='),
                    $this->createToken(false, false, true, false, 'new', $line),
                    $this->createToken(false, false, false, false, ';'),
                    $this->createToken(false, false, false, false, '$phrase2'),
                    $this->createToken(false, false, false, false, '='),
                    $this->createToken(true, false, false, false, '__', $line),
                    $this->createToken(false, true, false, false, '('),
                    $this->createToken(false, false, false, false, ';'),
                    false
                ],
                'getFunctionArgumentsTokensReturnValues' => [
                    [[$this->createToken(false, false, false, true, '\'Testing\'')]], // 'Testing')
                    [[$this->createToken(false, false, false, true, '\'More testing\'')]] // 'More testing')
                ],
                'isMatchingClassReturnValues' => [
                    true // \Magento\Framework\Phrase(
                ],
                'result' => [
                    [
                        'phrase' => '\'Testing\'',
                        'arguments' => 0,
                        'file' => $file,
                        'line' => $line
                    ],
                    [
                        'phrase' => '\'More testing\'',
                        'arguments' => 0,
                        'file' => $file,
                        'line' => $line
                    ]
                ]
            ]
        ];
    }

    /**
     * @param bool $isEqualFunctionReturnValue
     * @param bool $isOpenBraceReturnValue
     * @param bool $isNewReturnValue
     * @param bool $isConstantEncapsedString
     * @param string $value
     * @param int|null $line
     * @return Token|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createToken(
        $isEqualFunctionReturnValue,
        $isOpenBraceReturnValue,
        $isNewReturnValue,
        $isConstantEncapsedString,
        $value,
        $line = null
    ) {
        $token = $this->getMockBuilder(\Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\Token::class)
            ->disableOriginalConstructor()
            ->getMock();
        $token->expects($this->any())
            ->method('isEqualFunction')
            ->with('__')
            ->willReturn($isEqualFunctionReturnValue);
        $token->expects($this->any())
            ->method('isOpenBrace')
            ->willReturn($isOpenBraceReturnValue);
        $token->expects($this->any())
            ->method('isNew')
            ->willReturn($isNewReturnValue);
        $token->expects($this->any())
            ->method('isConstantEncapsedString')
            ->willReturn($isConstantEncapsedString);
        $token->expects($this->any())
            ->method('getValue')
            ->willReturn($value);
        $token->expects($this->any())
            ->method('getLine')
            ->willReturn($line);
        return $token;
    }

    public function testCollectPhrases()
    {
        $firstPart = "'first part'";
        $firstPartToken = new Token(\T_CONSTANT_ENCAPSED_STRING, $firstPart);
        $concatenationToken = new Token('.', '.');
        $secondPart = "' second part'";
        $secondPartToken = new Token(\T_CONSTANT_ENCAPSED_STRING, $secondPart);
        $phraseTokens = [$firstPartToken, $concatenationToken, $secondPartToken];
        $phraseString = "'first part' . ' second part'";

        $reflectionMethod = new \ReflectionMethod(
            \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector::class,
            '_collectPhrase'
        );

        $reflectionMethod->setAccessible(true);
        $this->assertSame($phraseString, $reflectionMethod->invoke($this->phraseCollector, $phraseTokens));
    }
}
