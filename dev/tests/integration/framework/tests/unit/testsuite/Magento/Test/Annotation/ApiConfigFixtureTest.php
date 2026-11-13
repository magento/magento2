<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Test\Annotation;

use Magento\Config\Model\ResourceModel\Config as ConfigResource;
use Magento\Framework\MessageQueue\PoisonPill\PoisonPillPutInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Annotation\ApiConfigFixture;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * Test class for \Magento\TestFramework\Annotation\ApiConfigFixture.
 */
class ApiConfigFixtureTest extends TestCase
{
    /**
     * @var ApiConfigFixture
     */
    private ApiConfigFixture $object;

    /**
     * @var ConfigResource|MockObject
     */
    private ConfigResource $configResource;

    /**
     * @var ReflectionMethod
     */
    private ReflectionMethod $restoreConfigData;

    /**
     * @var \ReflectionProperty
     */
    private \ReflectionProperty $valuesToDeleteFromDatabase;

    /**
     * @var \ReflectionProperty
     */
    private \ReflectionProperty $globalConfigValues;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $magentoRoot = dirname(__DIR__, 10);
        $apiFramework = $magentoRoot . '/dev/tests/api-functional/framework/Magento/TestFramework/';
        $integrationFramework = $magentoRoot . '/dev/tests/integration/framework/Magento/TestFramework/';

        spl_autoload_register(
            static function (string $class) use ($apiFramework, $integrationFramework): void {
                if (!str_starts_with($class, 'Magento\\TestFramework\\')) {
                    return;
                }
                $relative = str_replace('\\', '/', substr($class, strlen('Magento\\TestFramework\\'))) . '.php';
                foreach ([$apiFramework . $relative, $integrationFramework . $relative] as $file) {
                    if (is_file($file)) {
                        require_once $file;
                        return;
                    }
                }
            },
            true,
            true
        );

        $this->configResource = $this->createMock(ConfigResource::class);
        $poisonPill = $this->createMock(PoisonPillPutInterface::class);
        $poisonPill->method('put')->willReturn('pill');

        /** @var ObjectManagerInterface|MockObject $objectManager */
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->method('get')->willReturnCallback(
            function (string $type) use ($poisonPill) {
                if ($type === ConfigResource::class) {
                    return $this->configResource;
                }
                if ($type === PoisonPillPutInterface::class) {
                    return $poisonPill;
                }
                return $this->createMock($type);
            }
        );
        Bootstrap::setObjectManager($objectManager);

        $this->object = new ApiConfigFixture();
        $reflection = new ReflectionClass(ApiConfigFixture::class);
        $this->valuesToDeleteFromDatabase = $reflection->getProperty('valuesToDeleteFromDatabase');
        $this->globalConfigValues = $reflection->getProperty('globalConfigValues');
        $this->restoreConfigData = $reflection->getMethod('_restoreConfigData');
    }

    /**
     * After a delete-style restore, the next restore for the same path must write back, not delete.
     *
     * Reproduces the ApiConfigFixture lifetime bug: the delete list survived across tests.
     *
     * @return void
     */
    public function testRestoreConfigDataClearsValuesToDeleteFromDatabase(): void
    {
        $this->valuesToDeleteFromDatabase->setValue(
            $this->object,
            ['global' => ['general/store_information/phone' => '111']]
        );
        $this->globalConfigValues->setValue(
            $this->object,
            ['general/store_information/phone' => null]
        );

        $this->configResource->expects($this->once())
            ->method('deleteConfig')
            ->with('general/store_information/phone');

        $this->restoreConfigData->invoke($this->object);
        $this->assertSame([], $this->valuesToDeleteFromDatabase->getValue($this->object));

        $this->globalConfigValues->setValue(
            $this->object,
            ['general/store_information/phone' => '222']
        );
        $this->restoreConfigData->invoke($this->object);
        $this->assertSame([], $this->valuesToDeleteFromDatabase->getValue($this->object));
    }
}
