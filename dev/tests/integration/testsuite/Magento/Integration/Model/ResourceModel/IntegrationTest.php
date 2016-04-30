<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\ResourceModel;

/**
 * Integration test for \Magento\Integration\Model\ResourceModel\Integration
 */
class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Integration\Model\Integration
     */
    protected $integration;

    /**
     * @var \Magento\Integration\Model\Oauth\Consumer
     */
    protected $consumer;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->consumer = $objectManager->create('Magento\Integration\Model\Oauth\Consumer');
        $this->consumer->setData(
            [
                'key' => md5(uniqid()),
                'secret' => md5(uniqid()),
                'callback_url' => 'http://example.com/callback',
                'rejected_callback_url' => 'http://example.com/rejectedCallback'
            ]
        )->save();
        $this->integration = $objectManager->create('Magento\Integration\Model\Integration');
        $this->integration->setName('Test Integration')
            ->setConsumerId($this->consumer->getId())
            ->setStatus(\Magento\Integration\Model\Integration::STATUS_ACTIVE)
            ->save();
    }

    public function testLoadActiveIntegrationByConsumerId()
    {
        $integration = $this->integration->getResource()->selectActiveIntegrationByConsumerId($this->consumer->getId());
        $this->assertEquals($this->integration->getId(), $integration['integration_id']);
    }
}
