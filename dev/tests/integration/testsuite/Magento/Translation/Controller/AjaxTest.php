<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Controller;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Translation\Model\ResourceModel\StringUtils;

/**
 * Test for Magento\Translation\Controller\Ajax class.
 */
class AjaxTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @param array $postData
     * @param string $expected
     *
     * @return void
     * @dataProvider indexActionDataProvider
     * @magentoConfigFixture default_store dev/translate_inline/active 1
     */
    public function testIndexAction(array $postData, string $expected): void
    {
        $this->getRequest()->setPostValue('translate', $postData);
        $this->dispatch('translation/ajax/index');
        $result = $this->getResponse()->getBody();
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function indexActionDataProvider(): array
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
