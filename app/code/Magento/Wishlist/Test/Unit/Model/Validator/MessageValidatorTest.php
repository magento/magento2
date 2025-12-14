<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Model\Validator;

use Magento\Wishlist\Model\Validator\MessageValidator;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for MessageValidator
 */
class MessageValidatorTest extends TestCase
{
    /**
     * @var MessageValidator
     */
    private MessageValidator $validator;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->validator = new MessageValidator();
    }

    /**
     * Test that empty string is valid
     *
     * @return void
     */
    public function testEmptyStringIsValid(): void
    {
        $this->assertTrue($this->validator->isValid(''));
        $this->assertEmpty($this->validator->getMessages());
    }

    /**
     * Test that whitespace-only string is valid
     *
     * @return void
     */
    public function testWhitespaceOnlyStringIsValid(): void
    {
        $this->assertTrue($this->validator->isValid('   '));
        $this->assertEmpty($this->validator->getMessages());
    }

    /**
     * Test that null value is valid
     *
     * @return void
     */
    public function testNullValueIsValid(): void
    {
        $this->assertTrue($this->validator->isValid(null));
        $this->assertEmpty($this->validator->getMessages());
    }

    /**
     * Test that non-string value is valid
     *
     * @return void
     */
    public function testNonStringValueIsValid(): void
    {
        $this->assertTrue($this->validator->isValid(123));
        $this->assertTrue($this->validator->isValid([]));
        $this->assertTrue($this->validator->isValid(true));
        $this->assertEmpty($this->validator->getMessages());
    }

    /**
     * Test that valid messages pass validation
     *
     * @dataProvider validMessageDataProvider
     * @param string $message
     * @return void
     */
    public function testValidMessagesPass(string $message): void
    {
        $this->assertTrue($this->validator->isValid($message));
        $this->assertEmpty($this->validator->getMessages());
    }

    /**
     * Data provider for valid messages
     *
     * @return array
     */
    public static function validMessageDataProvider(): array
    {
        return [
            'simple_text' => ['Hello, this is a test message!'],
            'with_punctuation' => ['Check out my wishlist - it\'s amazing!'],
            'with_numbers' => ['I want 5 items for $99.99'],
            'multiline' => ["Hello\nThis is a multiline\nmessage"],
            'international' => ['Café José wants 50% off!'],
            'with_quotes' => ['He said "this is great"'],
            'with_apostrophe' => ["It's a wonderful product"],
            'with_email' => ['Contact me at test@example.com'],
            'with_url_safe' => ['Visit example.com for more info'],
        ];
    }

    /**
     * Test that PHP tags are rejected
     *
     * @dataProvider phpTagsDataProvider
     * @param string $message
     * @return void
     */
    public function testPhpTagsAreRejected(string $message): void
    {
        $this->assertFalse($this->validator->isValid($message));
        $this->assertNotEmpty($this->validator->getMessages());
    }

    /**
     * Data provider for PHP tags
     *
     * @return array
     */
    public static function phpTagsDataProvider(): array
    {
        return [
            'php_full' => ['<?php echo "test"; ?>'],
            'php_short' => ['<?= $var ?>'],
            'php_opening_only' => ['<? test'],
            'mixed_case' => ['<?PhP echo "test"; ?>'],
        ];
    }

    /**
     * Test that template directives are rejected
     *
     * @dataProvider templateDirectivesDataProvider
     * @param string $message
     * @return void
     */
    public function testTemplateDirectivesAreRejected(string $message): void
    {
        $this->assertFalse($this->validator->isValid($message));
        $this->assertNotEmpty($this->validator->getMessages());
    }

    /**
     * Data provider for template directives
     *
     * @return array
     */
    public static function templateDirectivesDataProvider(): array
    {
        return [
            'var_directive' => ['{{var this.getTemplateFilter()}}'],
            'if_directive' => ['{{if condition}}text{{/if}}'],
            'depend_directive' => ['{{depend variable}}text{{/depend}}'],
            'template_style' => ['{%if condition%}text{%endif%}'],
            'complex_directive' => ['{{var this.getTemplateFilter().filter("ls -al")}}'],
        ];
    }

    /**
     * Test that template object access patterns are rejected
     *
     * @dataProvider templateObjectAccessDataProvider
     * @param string $message
     * @return void
     */
    public function testTemplateObjectAccessIsRejected(string $message): void
    {
        $this->assertFalse($this->validator->isValid($message));
        $this->assertNotEmpty($this->validator->getMessages());
    }

    /**
     * Data provider for template object access patterns
     *
     * @return array
     */
    public static function templateObjectAccessDataProvider(): array
    {
        return [
            'this_get_method' => ['this.getTemplateFilter()'],
            'template_filter' => ['getTemplateFilter()'],
            'filter_callback' => ['addAfterFilterCallback("test")'],
        ];
    }

    /**
     * Test URL-encoded attacks are caught
     *
     * @dataProvider urlEncodedAttacksDataProvider
     * @param string $message
     * @return void
     */
    public function testUrlEncodedAttacksAreCaught(string $message): void
    {
        $this->assertFalse($this->validator->isValid($message));
        $this->assertNotEmpty($this->validator->getMessages());
    }

    /**
     * Data provider for URL-encoded attacks
     *
     * @return array
     */
    public static function urlEncodedAttacksDataProvider(): array
    {
        return [
            'encoded_template' => ['%7B%7Bvar%20test%7D%7D'],
            'encoded_php' => ['%3C%3Fphp%20echo%20%22test%22%3B%20%3F%3E'],
        ];
    }

    /**
     * Test newline obfuscation attacks are caught
     *
     * @dataProvider newlineObfuscationDataProvider
     * @param string $message
     * @return void
     */
    public function testNewlineObfuscationIsCaught(string $message): void
    {
        $this->assertFalse($this->validator->isValid($message));
        $this->assertNotEmpty($this->validator->getMessages());
    }

    /**
     * Data provider for newline obfuscation attacks
     *
     * @return array
     */
    public static function newlineObfuscationDataProvider(): array
    {
        return [
            'template_with_newlines' => ["{{var this.getTempl\r\nateFilter()}}"],
            'php_with_newlines' => ["<?ph\rp echo 'test'; ?>"],
        ];
    }

    /**
     * Test the actual attack
     *
     * @return void
     */
    public function testGithubIssue39024AttackIsBlocked(): void
    {
        $attack = '{{var this.getTempl%0d%0aateFilter().filter(%22ls -al%22)}}' .
                  '{{if this.getTempla%0d%0ateFilter().addAft%0d%0aerFilterCallback(%22SySTeM%22)' .
                  '.filter(%22ls -al%22)}}{{/if}}';
        
        $this->assertFalse($this->validator->isValid($attack));
        $this->assertNotEmpty($this->validator->getMessages());
        $this->assertStringContainsString('Invalid content detected', $this->validator->getMessages()[0]);
    }

    /**
     * Test that a new validator instance has no messages
     *
     * @return void
     */
    public function testNewValidatorInstanceHasNoMessages(): void
    {
        $validator1 = new MessageValidator();
        
        // First validation fails
        $this->assertFalse($validator1->isValid('{{var test}}'));
        $this->assertNotEmpty($validator1->getMessages());
        
        // New validator instance should have no messages
        $validator2 = new MessageValidator();
        $this->assertTrue($validator2->isValid('Valid message'));
        $this->assertEmpty($validator2->getMessages());
    }

    /**
     * Test complex valid message with special characters
     *
     * @return void
     */
    public function testComplexValidMessage(): void
    {
        $message = "Hi! Check out my wishlist :)\n\n" .
                   "I really love these items - especially the one for $49.99!\n" .
                   "Let me know what you think at my@email.com\n\n" .
                   "Thanks!";
        
        $this->assertTrue($this->validator->isValid($message));
        $this->assertEmpty($this->validator->getMessages());
    }

    /**
     * Test that legitimate use of word "filter" is allowed
     *
     * @return void
     */
    public function testLegitimateFilterWordIsAllowed(): void
    {
        $message = "I need a water filter for my home.";
        $this->assertTrue($this->validator->isValid($message));
        $this->assertEmpty($this->validator->getMessages());
    }

    /**
     * Test multiple forbidden patterns in one message
     *
     * @return void
     */
    public function testMultipleForbiddenPatterns(): void
    {
        $message = '{{var test}}<?php echo "test"; ?>';
        
        $this->assertFalse($this->validator->isValid($message));
        $this->assertNotEmpty($this->validator->getMessages());
    }
}
