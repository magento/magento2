<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Controller;

class AjaxTest extends \Magento\TestFramework\TestCase\AbstractController
{
    protected function setUp()
    {
        /* Called getConfig as workaround for setConfig bug */
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Store\Model\StoreManagerInterface'
        )->getStore(
            'default'
        )->getConfig(
            'dev/translate_inline/active'
        );
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\Config\MutableScopeConfigInterface'
        )->setValue(
            'dev/translate_inline/active',
            true,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            'default'
        );
        parent::setUp();
    }

    /**
     * @dataProvider indexActionDataProvider
     */
    public function testIndexAction($postData, $expected)
    {
        $this->getRequest()->setPostValue('translate', $postData);
        $this->dispatch('translation/ajax/index');
        $this->assertEquals($expected, $this->getResponse()->getBody());
    }

    public function indexActionDataProvider()
    {
        return [
            [
                [
                    [
                        'original' => 'phrase1',
                        'custom' => 'translation1'
                    ]
                ],
                '{"phrase1":"translation1"}'
            ],
            [
                [
                    [
                        'original' => 'phrase2',
                        'custom' => 'translation2'
                    ]
                ],
                '{"phrase1":"translation1","phrase2":"translation2"}'
            ]
        ];
    }
}
