<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Config\Controller\Adminhtml\System;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 */
class ConfigTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    public function testEditAction()
    {
        $this->dispatch('backend/admin/system_config/edit');
        $this->assertContains('<div id="system_config_tabs"', $this->getResponse()->getBody());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testChangeBaseUrl()
    {
        $defaultHost = Bootstrap::getObjectManager()->create('Magento\Framework\Url')->getBaseUrl();
        $newHost = 'm2test123.loc';
        $request = $this->getRequest();
        $request->setPostValue(
            ['groups' =>
                ['unsecure' =>
                    ['fields' =>
                        ['base_url' =>
                            ['value' => 'http://' . $newHost . '/']
                        ]
                    ]
                ],
            'config_state' =>
                ['web_unsecure' => 1]
            ]
        )->setParam(
            'section',
            'web'
        );
        $this->dispatch('backend/admin/system_config/save');

        $this->assertTrue($this->getResponse()->isRedirect(), 'Redirect was expected, but none was performed.');

        /** @var array|bool $url */
        $url = parse_url($this->getResponse()->getHeader('Location')->getFieldValue());
        $this->assertArrayNotHasKey(
            'query',
            $url,
            'No GET params, including "SID", were expected, but somewhat exists'
        );
        $this->assertEquals($newHost, $url['host'], 'A new host in the url expected, but there is old one');
        $this->resetBaseUrl($defaultHost);
    }

    /**
     * Reset test framework default base url
     */
    protected function resetBaseUrl($defaultHost)
    {
        $baseUrlData = [
            'section' => 'web',
            'website' => NULL,
            'store' => NULL,
            'groups' => [
                'unsecure' => [
                    'fields' => [
                        'base_url' => [
                            'value' => $defaultHost
                        ]
                    ]
                ]
            ]
        ];
        Bootstrap::getObjectManager()->create('Magento\Config\Model\Config\Factory')
            ->create()
            ->addData($baseUrlData)
            ->save();
    }
}
