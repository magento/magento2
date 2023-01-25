<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for remember me checkbox on create customer account page.
 *
 * @see \Magento\Persistent\Model\CheckoutConfigProvider
 */
class CheckoutConfigProviderTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CheckoutConfigProvider */
    private $model;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->model = $this->objectManager->get(CheckoutConfigProvider::class);
    }

    /**
     * @magentoConfigFixture current_store persistent/options/enabled 1
     * @magentoConfigFixture current_store persistent/options/remember_enabled 1
     * @magentoConfigFixture current_store persistent/options/remember_default 1
     *
     * @return void
     */
    public function testRememberMeEnabled(): void
    {
        $expectedConfig = [
            'persistenceConfig' => ['isRememberMeCheckboxVisible' => true, 'isRememberMeCheckboxChecked' => true],
        ];
        $config = $this->model->getConfig();
        $this->assertEquals($expectedConfig, $config);
    }

    /**
     * @magentoConfigFixture current_store persistent/options/enabled 1
     * @magentoConfigFixture current_store persistent/options/remember_enabled 0
     * @magentoConfigFixture current_store persistent/options/remember_default 0
     *
     * @return void
     */
    public function testRememberMeDisabled(): void
    {
        $expectedConfig = [
            'persistenceConfig' => ['isRememberMeCheckboxVisible' => false, 'isRememberMeCheckboxChecked' => false],
        ];
        $config = $this->model->getConfig();
        $this->assertEquals($expectedConfig, $config);
    }

    /**
     * @magentoConfigFixture current_store persistent/options/enabled 0
     * @magentoConfigFixture current_store persistent/options/remember_default 0
     *
     * @return void
     */
    public function testPersistentDisabled(): void
    {
        $expectedConfig = [
            'persistenceConfig' => ['isRememberMeCheckboxVisible' => false, 'isRememberMeCheckboxChecked' => false],
        ];
        $config = $this->model->getConfig();
        $this->assertEquals($expectedConfig, $config);
    }
}
