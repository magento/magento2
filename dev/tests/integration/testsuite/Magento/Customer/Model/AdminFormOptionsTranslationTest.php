<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

use Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses;
use Magento\Framework\Phrase;
use Magento\Framework\Phrase\RendererInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Option labels of the admin customer form are translated.
 *
 * @magentoAppArea adminhtml
 */
class AdminFormOptionsTranslationTest extends TestCase
{
    /**
     * @var RendererInterface
     */
    private $defaultRenderer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->defaultRenderer = Phrase::getRenderer();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        Phrase::setRenderer($this->defaultRenderer);
    }

    /**
     * The gender options use the translations of "Male", "Female" and "Not Specified".
     *
     * @return void
     */
    public function testGenderOptionsAreTranslated(): void
    {
        $translations = [
            'Male' => 'Translated Male',
            'Female' => 'Translated Female',
            'Not Specified' => 'Translated Not Specified',
        ];
        $renderer = $this->createStub(RendererInterface::class);
        $renderer->method('render')->willReturnCallback(
            fn (array $source, array $arguments) => $translations[end($source)]
                ?? $this->defaultRenderer->render($source, $arguments)
        );
        Phrase::setRenderer($renderer);

        $dataProvider = Bootstrap::getObjectManager()->create(
            DataProviderWithDefaultAddresses::class,
            [
                'name' => 'customer_form_data_source',
                'primaryFieldName' => 'entity_id',
                'requestFieldName' => 'id',
            ]
        );
        $meta = $dataProvider->getMeta();
        $options = $meta['customer']['children']['gender']['arguments']['data']['config']['options'];
        $labels = array_column($options, 'label');

        foreach ($translations as $translation) {
            $this->assertContains($translation, $labels);
        }
        $this->assertNotContains('Male', $labels);
    }
}
