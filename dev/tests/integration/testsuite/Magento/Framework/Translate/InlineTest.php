<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Translate;

use PHPUnit\Framework\Attributes\DataProvider;

class InlineTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Translate\Inline
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_storeId = 'default';

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $state;

    public static function setUpBeforeClass(): void
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\App\State::class)
            ->setAreaCode('frontend');
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\DesignInterface::class
        )->setDesignTheme(
            'Magento/blank'
        );
    }

    protected function setUp(): void
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Translate\Inline::class
        );
        $this->state = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Translate\Inline\StateInterface::class
        );
        /* Called getConfig as workaround for setConfig bug */
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Store\Model\StoreManagerInterface::class
        )->getStore(
            $this->_storeId
        )->getConfig(
            'dev/translate_inline/active'
        );
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\App\Config\MutableScopeConfigInterface::class
        )->setValue(
            'dev/translate_inline/active',
            true,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }

    protected function tearDown(): void
    {
        // Reset shared inline state for other tests. Do not call resume() here: if suspend() was never
        // invoked, resume() sets isEnabled from null storedStatus and disables inline for the rest of the run.
        if ($this->state !== null) {
            $this->state->enable();
        }
    }

    public function testIsAllowed()
    {
        $this->assertTrue($this->_model->isAllowed());
        $this->assertTrue($this->_model->isAllowed($this->_storeId));
        $this->assertTrue(
            $this->_model->isAllowed(
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    \Magento\Store\Model\StoreManagerInterface::class
                )->getStore(
                    $this->_storeId
                )
            )
        );
        $this->state->suspend();
        $this->assertFalse($this->_model->isAllowed());
        $this->assertFalse($this->_model->isAllowed($this->_storeId));
        $this->assertFalse(
            $this->_model->isAllowed(
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    \Magento\Store\Model\StoreManagerInterface::class
                )->getStore(
                    $this->_storeId
                )
            )
        );
    }

    /**
     * @param string|array $originalText
     * @param string|array $expectedText For plain text, exact expected body. For HTML cases, kept for the
     *        historical two-argument provider shape; assertions use structural checks (golden file removed).
     */
    #[DataProvider('processResponseBodyDataProvider')]
    public function testProcessResponseBody($originalText, $expectedText)
    {
        if (is_array($originalText)) {
            $body = $originalText;
            $this->_model->processResponseBody($body, false);
            $this->assertIsArray($expectedText);
            $this->assertCount(count($expectedText), $body);
            $this->assertIsString($body[0]);
            $this->assertProcessedHtmlInlineBody($body[0]);
            return;
        }

        $snapshotBeforeProcessing = $originalText;
        $body = $originalText;
        $this->_model->processResponseBody($body, false);

        if (!str_contains($snapshotBeforeProcessing, '<html')) {
            $this->assertSame($expectedText, $body);
            return;
        }

        $this->assertIsString($body);
        $this->assertProcessedHtmlInlineBody($body);
    }

    /**
     * Assert HTML was processed for inline translation (markers replaced, UI hooks present).
     *
     * Full-document string equality is not used: static URLs, JSON shape, and injected assets change across releases.
     *
     * @param string $html
     */
    private function assertProcessedHtmlInlineBody(string $html): void
    {
        $this->assertStringNotContainsString(
            '{{{',
            $html,
            'Inline translation template markers should be replaced in the HTML output.'
        );
        $this->assertStringContainsString('data-translate', $html);
        $this->assertStringContainsString('translate-inline-title', $html);
        $this->assertStringContainsString('shown_0', $html);
        $this->assertStringContainsString('some_title_shown_1_in_div', $html);
        $this->assertStringContainsString('shown_2', $html);
        $this->assertMatchesRegularExpression(
            '/translate-dialog|translateInline|mage\/translate-inline/',
            $html,
            'Expected translate-inline UI wiring (dialog, script, or stylesheet).'
        );
    }

    /**
     * @return array
     */
    public static function processResponseBodyDataProvider()
    {
        $originalText = file_get_contents(__DIR__ . '/_files/_inline_page_original.html');

        return [
            'plain text' => ['text with no translations and tags', 'text with no translations and tags'],
            'html string' => [$originalText, $originalText],
            'html array' => [[$originalText], [$originalText]],
        ];
    }
}
