<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Plugin\Ui\Component\Form\Element;

use Magento\Customer\Model\Config\Share as ConfigShare;
use Magento\Customer\Plugin\Ui\Component\Form\Element\DisableStoreViewWebsiteFilter;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Element\Select;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for the plugin that removes the website filter from the welcome-email store-view select.
 */
class DisableStoreViewWebsiteFilterTest extends TestCase
{
    private const FIELD_NAME = 'sendemail_store_id';
    private const FORM_NAMESPACE = 'customer_form';

    /**
     * @var ConfigShare|MockObject
     */
    private $configShare;

    /**
     * @var DisableStoreViewWebsiteFilter
     */
    private DisableStoreViewWebsiteFilter $plugin;

    protected function setUp(): void
    {
        $this->configShare = $this->createMock(ConfigShare::class);
        $this->plugin = new DisableStoreViewWebsiteFilter($this->configShare);
    }

    /**
     * In Global scope, on the target field/namespace, the filterBy config is removed.
     *
     * @return void
     */
    public function testRemovesFilterByInGlobalScope(): void
    {
        $this->configShare->expects($this->once())->method('isWebsiteScope')->willReturn(false);

        $subject = $this->createSelect(self::FIELD_NAME, self::FORM_NAMESPACE, [
            'filterBy' => ['field' => 'website_id', 'target' => 'website_id'],
            'options' => [],
        ]);
        $subject->expects($this->once())
            ->method('setData')
            ->with('config', ['options' => []]);
        $this->plugin->beforePrepare($subject);
    }

    /**
     * In Website scope the filter is left intact even on the target field.
     *
     * @return void
     */
    public function testKeepsFilterByInWebsiteScope(): void
    {
        $this->configShare->expects($this->once())->method('isWebsiteScope')->willReturn(true);
        $subject = $this->createSelect(self::FIELD_NAME, self::FORM_NAMESPACE, [
            'filterBy' => ['field' => 'website_id', 'target' => 'website_id'],
        ]);
        $subject->expects($this->never())->method('setData');
        $this->plugin->beforePrepare($subject);
    }

    /**
     * A select with a different name is never touched (and scope is not even evaluated).
     *
     * @return void
     */
    public function testIgnoresOtherFieldNames(): void
    {
        $this->configShare->expects($this->never())->method('isWebsiteScope');
        $subject = $this->createMock(Select::class);
        $subject->method('getName')->willReturn('some_other_select');
        $subject->expects($this->never())->method('getContext');
        $subject->expects($this->never())->method('setData');
        $this->plugin->beforePrepare($subject);
    }

    /**
     * The target field name outside the customer form namespace is not touched.
     *
     * @return void
     */
    public function testIgnoresOtherNamespaces(): void
    {
        $this->configShare->expects($this->never())->method('isWebsiteScope');

        $subject = $this->createSelect(self::FIELD_NAME, 'some_other_form', [
            'filterBy' => ['field' => 'website_id', 'target' => 'website_id'],
        ]);
        $subject->expects($this->never())->method('setData');

        $this->plugin->beforePrepare($subject);
    }

    /**
     * When the config has no filterBy in Global scope, nothing is written.
     *
     * @return void
     */
    public function testDoesNothingWhenFilterByAbsent(): void
    {
        $this->configShare->expects($this->once())->method('isWebsiteScope')->willReturn(false);

        $subject = $this->createSelect(self::FIELD_NAME, self::FORM_NAMESPACE, ['options' => []]);
        $subject->expects($this->never())->method('setData');

        $this->plugin->beforePrepare($subject);
    }

    /**
     * Build a Select mock returning the given name, namespace and config.
     *
     * @param string $name
     * @param string $namespace
     * @param array $config
     * @return Select|MockObject
     */
    private function createSelect(string $name, string $namespace, array $config): Select
    {
        $context = $this->createStub(ContextInterface::class);
        $context->method('getNamespace')->willReturn($namespace);

        $subject = $this->createMock(Select::class);
        $subject->method('getName')->willReturn($name);
        $subject->method('getContext')->willReturn($context);
        $subject->method('getData')->with('config')->willReturn($config);

        return $subject;
    }
}
