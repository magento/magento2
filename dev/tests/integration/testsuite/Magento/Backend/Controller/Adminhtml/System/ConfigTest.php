<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Controller\Adminhtml\System;

/**
 * @magentoAppArea adminhtml
 */
class ConfigTest extends \Magento\Backend\Utility\Controller
{
    public function testEditAction()
    {
        $this->dispatch('backend/admin/system_config/edit');
        $this->assertContains('<ul id="system_config_tabs"', $this->getResponse()->getBody());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testChangeBaseUrl()
    {
        $newHost = 'm2test123.loc';
        $request = $this->getRequest();
        $request->setPost(
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
        $url = parse_url($this->getResponse()->getHeader('Location')['value']);
        $this->assertArrayNotHasKey('query', $url, 'No GET params were expected, but somewhat exists');
        $this->assertEquals($newHost, $url['host'], 'A new host in the url expected, but there is old one');
    }
}

