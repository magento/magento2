<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\App\RequestInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\FormFactory;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;

/**
 * Test UI component context.
 */
class ContextTest extends TestCase
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ContextFactory
     */
    private $contextFactory;

    /**
     * @var FormFactory
     */
    private $componentFactory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->request = Bootstrap::getObjectManager()->get(RequestInterface::class);
        $this->contextFactory = Bootstrap::getObjectManager()->get(ContextFactory::class);
        $this->componentFactory = Bootstrap::getObjectManager()->get(FormFactory::class);
    }

    /**
     * Generate provider for the test.
     *
     * @return DataProviderInterface
     */
    private function generateMockProvider(): DataProviderInterface
    {
        /** @var DataProviderInterface|MockObject $mock */
        $mock = $this->getMockForAbstractClass(DataProviderInterface::class);
        $mock->method('getName')->willReturn('test');
        $mock->method('getPrimaryFieldName')->willReturn('id');
        $mock->method('getRequestFieldName')->willReturn('id');
        $mock->method('getData')->willReturn(['id' => ['some_field' => '${\'some_value\'}']]);
        $mock->method('getConfigData')->willReturn([]);
        $mock->method('getFieldMetaInfo')->willReturn([]);
        $mock->method('getFieldSetMetaInfo')->willReturn('id');
        $mock->method('getFieldsMetaInfo')->willReturn('id');
        $mock->method('getSearchCriteria')->willReturn(new SearchCriteria());
        $mock->method('getSearchResult')->willReturn([]);

        return $mock;
    }

    /**
     * Check processed provider data.
     *
     * @return void
     */
    public function testGetDataSourceData(): void
    {
        $dataProvider = $this->generateMockProvider();
        $context = $this->contextFactory->create(['dataProvider' => $dataProvider]);
        /** @var Form $component */
        $component = $this->componentFactory->create(['context' => $context]);
        $this->request->setParams(['id' => 'id']);

        $data = $context->getDataSourceData($component);
        $this->assertEquals(
            [
                'test' => [
                    'type' => 'dataSource',
                    'name' => 'test',
                    'dataScope' => null,
                    'config' => [
                        'data' => ['some_field' => '${\'some_value\'}', '__disableTmpl' => ['some_field' => true]],
                        'params' => [
                            'namespace' => null,
                            'id' => 'id'
                        ]]
                ]
            ],
            $data
        );
    }
}
