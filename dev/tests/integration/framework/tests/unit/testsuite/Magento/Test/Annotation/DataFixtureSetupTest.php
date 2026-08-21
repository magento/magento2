<?php

namespace integration\framework\tests\unit\testsuite\Magento\Test\Annotation;

use Magento\Framework\Registry;
use Magento\TestFramework\Annotation\DataFixtureSetup;
use Magento\TestFramework\Fixture\DataFixtureFactory;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\ScopeSwitcherInterface;
use PHPUnit\Framework\TestCase;

class DataFixtureSetupTest extends TestCase
{
    private ?DataFixtureSetup $object;

    private ?Registry $registry;
    private ?DataFixtureFactory $dataFixtureFactory;
    private ?ScopeSwitcherInterface $scopeSwitcher;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(Registry::class);
        $this->dataFixtureFactory = $this->createMock(DataFixtureFactory::class);
        $this->scopeSwitcher = $this->createMock(ScopeSwitcherInterface::class);
        $this->object = new DataFixtureSetup($this->registry, $this->dataFixtureFactory, $this->scopeSwitcher);
    }

    public function testApplyWithArbitraryScopeThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Scope "non_existing_scope" does not exist.');

        $storage = new DataFixtureStorage();
        DataFixtureStorageManager::setStorage($storage);

        $this->object->apply([
            'data' => [],
            'factory' => 'DummyFixtureClass',
            'scope' => 'non_existing_scope',
        ]);
    }
}
