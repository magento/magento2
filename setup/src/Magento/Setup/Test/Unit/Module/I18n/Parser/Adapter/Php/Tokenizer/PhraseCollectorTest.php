<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\I18n\Parser\Adapter\Php\Tokenizer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer;
use Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector;
use Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\Token;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector
 */
class PhraseCollectorTest extends TestCase
{
    /**
     * @var PhraseCollector
     */
    protected $phraseCollector;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Tokenizer|MockObject
     */
    protected $tokenizerMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->tokenizerMock = $this->getMockBuilder(Tokenizer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->phraseCollector = $this->objectManager->getObject(
            PhraseCollector::class,
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
        $nextRealToken = [];
        foreach ($getNextRealTokenReturnValues as $key => $token) {
            if (is_callable($token)) {
                $nextRealToken[$key] = $token($this);
            } else {
                $nextRealToken[$key] = $token;
            }
        }

        foreach ($getFunctionArgumentsTokensReturnValues as &$returnToken) {
            if (is_callable($returnToken[0][0])) {
                $returnToken[0][0] = $returnToken[0][0]($this);
            }
        }

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
                $nextRealToken
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
    public static function testParseDataProvider()
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
                    static fn (self $testCase) => $testCase->createToken(false, false, false, false, '$phrase1'),
                    static fn (self $testCase) => $testCase->createToken(false, false, false, false, '='),
                    static fn (self $testCase) => $testCase->createToken(false, false, true, false, 'new', $line),
                    static fn (self $testCase) => $testCase->createToken(false, false, false, false, ';'),
                    static fn (self $testCase) => $testCase->createToken(false, false, false, false, '$phrase2'),
                    static fn (self $testCase) => $testCase->createToken(false, false, false, false, '='),
                    static fn (self $testCase) => $testCase->createToken(true, false, false, false, '__', $line),
                    static fn (self $testCase) => $testCase->createToken(false, true, false, false, '('),
                    static fn (self $testCase) => $testCase->createToken(false, false, false, false, ';'),
                    false
                ],
                'getFunctionArgumentsTokensReturnValues' => [
                    [[static fn (self $testCase) => $testCase->createToken(
                        false,
                        false,
                        false,
                        true,
                        '\'Testing\''
                    )]], // 'Testing')
                    [[static fn (self $testCase) => $testCase->createToken(
                        false,
                        false,
                        false,
                        true,
                        '\'More testing\''
                    )]] // 'More testing')
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
     * @return Token|MockObject
     */
    protected function createToken(
        $isEqualFunctionReturnValue,
        $isOpenBraceReturnValue,
        $isNewReturnValue,
        $isConstantEncapsedString,
        $value,
        $line = null
    ) {
        $token = $this->getMockBuilder(Token::class)
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
            PhraseCollector::class,
            '_collectPhrase'
        );

        $reflectionMethod->setAccessible(true);
        $this->assertSame($phraseString, $reflectionMethod->invoke($this->phraseCollector, $phraseTokens));
    }
}
