<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Controller;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\ObjectManagerInterface;
use Magento\Translation\Model\ResourceModel\StringUtils;

/**
 * Test for Magento\Translation\Controller\Ajax class.
 */
class AjaxTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        /* Called getConfig as workaround for setConfig bug */
        $this->objectManager = Bootstrap::getObjectManager();
        $this->objectManager->get(
            StoreManagerInterface::class
        )->getStore(
            'default'
        )->getConfig(
            'dev/translate_inline/active'
        );
        $this->objectManager->get(
            MutableScopeConfigInterface::class
        )->setValue(
            'dev/translate_inline/active',
            true,
            ScopeInterface::SCOPE_STORE,
            'default'
        );
        parent::setUp();
    }

    /**
     * @param array $postData
     * @param string $expected
     *
     * @return void
     * @dataProvider indexActionDataProvider
     */
    public function testIndexAction(array $postData, string $expected): void
    {
        $this->getRequest()->setPostValue('translate', $postData);
        $this->dispatch('translation/ajax/index');
        $this->assertEquals($expected, $this->getResponse()->getBody());
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
     * @inheritdoc
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
