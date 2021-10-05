<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Integration\Model\ResourceModel\Oauth;

use Magento\Framework\Oauth\Helper\Oauth;
use Magento\Integration\Model\Oauth\Consumer as ConsumerModel;
use Magento\Framework\ObjectManagerInterface;

/**
 * Integration test for @see \Magento\Integration\Model\ResourceModel\Oauth\Consumer
 *
 */
class ConsumerTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var ConsumerModel
     */
    private $consumerModel;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Oauth
     */
    private $oauthHelper;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->oauthHelper = $this->objectManager->create(Oauth::class);
        $this->consumerModel = $this->objectManager->create(ConsumerModel::class);
        parent::setUp();
    }

    public function testSave(): void
    {
        $consumerSecret = $this->oauthHelper->generateConsumerSecret();
        $consumerKey = $this->oauthHelper->generateConsumerKey();
        $this->consumerModel->setData(
            [
                'key' => $consumerKey,
                'secret' => $consumerSecret,
            ]
        )->save();

        $consumerResourceModel = $this->consumerModel->getResource();

        $this->assertEquals($consumerSecret, $this->consumerModel->getSecret());
        $this->assertNotEquals(
            $this->consumerModel->getSecret(),
            $consumerResourceModel->load($this->consumerModel, 'secret')
        );
    }
}
