<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace Magento\Integration\Block\Adminhtml\Integration;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for \Magento\Integration\Block\Adminhtml\Integration\Tokens
 *
 * @magentoAppArea adminhtml
 */
class TokensTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Integration\Block\Adminhtml\Integration\Tokens
     */
    protected $tokensBlock;

    protected function setUp(): void
    {
        $this->tokensBlock = Bootstrap::getObjectManager()
            ->create(\Magento\Integration\Block\Adminhtml\Integration\Tokens::class);
    }

    public function testGetFormFields()
    {
        $expectedData = [
            [
                'name' => Tokens::DATA_CONSUMER_KEY,
                'type' => 'text',
                'metadata' => [
                    'label' => __('Consumer Key'),
                    'name' => Tokens::DATA_CONSUMER_KEY,
                    'readonly' => true,
                ],
            ],
            [
                'name' => Tokens::DATA_CONSUMER_SECRET,
                'type' => 'text',
                'metadata' => [
                    'label' => __('Consumer Secret'),
                    'name' => Tokens::DATA_CONSUMER_SECRET,
                    'readonly' => true,
                ]
            ],
            [
                'name' => Tokens::DATA_TOKEN,
                'type' => 'text',
                'metadata' => ['label' => __('Access Token'), 'name' => Tokens::DATA_TOKEN, 'readonly' => true]
            ],
            [
                'name' => Tokens::DATA_TOKEN_SECRET,
                'type' => 'text',
                'metadata' => [
                    'label' => __('Access Token Secret'),
                    'name' => Tokens::DATA_TOKEN_SECRET,
                    'readonly' => true,
                ]
            ]
        ];
        $this->assertEquals($expectedData, $this->tokensBlock->getFormFields());
    }

    public function testToHtml()
    {
        $htmlContent = $this->tokensBlock->toHtml();

        $this->assertStringContainsString('name="consumer_key"', $htmlContent);
        $this->assertStringContainsString(
            '<span>Consumer Key</span>',
            $htmlContent,
            "HTML content of token block should contain information about 'Consumer Key'."
        );

        $this->assertStringContainsString('name="consumer_secret"', $htmlContent);
        $this->assertStringContainsString(
            '<span>Consumer Secret</span>',
            $htmlContent,
            "HTML content of token block should contain information about 'Consumer Secret'."
        );

        $this->assertStringContainsString('name="token"', $htmlContent);
        $this->assertStringContainsString(
            '<span>Access Token</span>',
            $htmlContent,
            "HTML content of token block should contain information about 'Access Token'."
        );

        $this->assertStringContainsString('name="token_secret"', $htmlContent);
        $this->assertStringContainsString(
            '<span>Access Token Secret</span>',
            $htmlContent,
            "HTML content of token block should contain information about 'Access Token Secret'."
        );
    }
}
