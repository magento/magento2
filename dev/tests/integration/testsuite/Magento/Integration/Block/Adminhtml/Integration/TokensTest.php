<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
class TokensTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Integration\Block\Adminhtml\Integration\Tokens
     */
    protected $tokensBlock;

    protected function setUp()
    {
        $this->tokensBlock = Bootstrap::getObjectManager()
            ->create('Magento\Integration\Block\Adminhtml\Integration\Tokens');
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

        $this->assertContains('name="consumer_key"', $htmlContent);
        $this->assertContains(
            '<span>Consumer Key</span>',
            $htmlContent,
            "HTML content of token block should contain information about 'Consumer Key'."
        );

        $this->assertContains('name="consumer_secret"', $htmlContent);
        $this->assertContains(
            '<span>Consumer Secret</span>',
            $htmlContent,
            "HTML content of token block should contain information about 'Consumer Secret'."
        );

        $this->assertContains('name="token"', $htmlContent);
        $this->assertContains(
            '<span>Access Token</span>',
            $htmlContent,
            "HTML content of token block should contain information about 'Access Token'."
        );

        $this->assertContains('name="token_secret"', $htmlContent);
        $this->assertContains(
            '<span>Access Token Secret</span>',
            $htmlContent,
            "HTML content of token block should contain information about 'Access Token Secret'."
        );
    }
}
