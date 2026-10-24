<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Translation\Controller;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Translation\Model\ResourceModel\StringUtils;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test for Magento\Translation\Controller\Ajax class.
 *
 * @magentoDbIsolation disabled
 */
class AjaxTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @magentoConfigFixture default_store dev/translate_inline/active 1
     */
    #[DataProvider('indexActionDataProvider')]
    public function testIndexAction(array $postData, string $expected): void
    {
        $this->getRequest()->setPostValue('translate', $postData);
        $this->dispatch('translation/ajax/index');
        $result = $this->getResponse()->getBody();
        $this->assertEquals($expected, $result);
    }

    public static function indexActionDataProvider(): array
    {
        return [
            [
                [
                    [
                        'original' => 'phrase with &',
                        'custom' => 'phrase with & translated',
                    ],
                ],
                '{"phrase with &":"phrase with & translated"}',
            ],
            [
                [
                    [
                        'original' => 'phrase with &',
                        'custom' => 'phrase with & translated (updated)',
                    ],
                ],
                '{"phrase with &":"phrase with & translated (updated)"}',
            ],
            [
                [
                    [
                        'original' => 'phrase with &',
                        'custom' => 'phrase with &',
                    ],
                ],
                '[]',
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public static function tearDownAfterClass(): void
    {
        try {
            Bootstrap::getObjectManager()->get(StringUtils::class)->deleteTranslate('phrase with &');
        } catch (NoSuchEntityException $exception) {
            //translate already deleted
        }
        parent::tearDownAfterClass();
    }
}
