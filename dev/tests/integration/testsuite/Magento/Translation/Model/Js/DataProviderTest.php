<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Translation\Model\Js;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TranslateInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Translation\Model\ResourceModel\StringUtils;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for \Magento\Translation\Model\Js\DataProvider class.
 */
class DataProviderTest extends TestCase
{
    /**
     * @var StringUtils
     */
    private $stringUtils;

    /**
     * @var TranslateInterface
     */
    private $translate;

    /**
     * @var DataProviderInterface
     */
    private $translationDataProvider;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->stringUtils = $objectManager->get(StringUtils::class);
        $this->translate = $objectManager->get(TranslateInterface::class);
        $this->translationDataProvider = $objectManager->get(DataProviderInterface::class);
    }

    /**
     * Test translation data.
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture default_store dev/translate_inline/active 1
     */
    public function testGetData()
    {
        $expectedDictionary = ['Proceed to Checkout' => 'Proceed to Checkout - Translated'];

        $this->stringUtils->saveTranslate('Proceed to Checkout', 'Proceed to Checkout - Translated');
        $this->translate->setLocale('en_US')->loadData('frontend', true);
        $dictionary = $this->translationDataProvider->getData('Magento/luma');
        $this->assertEquals($expectedDictionary, $dictionary);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        try {
            $this->stringUtils->deleteTranslate('Proceed to Checkout');
        } catch (NoSuchEntityException $exception) {
            // translate already deleted
        }
    }
}
