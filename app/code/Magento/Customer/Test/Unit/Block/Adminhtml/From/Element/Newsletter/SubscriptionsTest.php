<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block\Adminhtml\From\Element\Newsletter;

use Magento\Customer\Block\Adminhtml\Form\Element\Newsletter\Subscriptions;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Customer Newsletter Subscriptions Element
 */
class SubscriptionsTest extends TestCase
{
    /**
     * @var Factory|MockObject
     */
    private $factoryElement;

    /**
     * @var CollectionFactory|MockObject
     */
    private $factoryCollection;

    /**
     * @var Escaper|MockObject
     */
    private $escaper;

    /**
     * @var DataPersistorInterface|MockObject
     */
    private $dataPersistor;

    /**
     * @var Subscriptions
     */
    private $element;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->factoryElement = $this->createMock(Factory::class);
        $this->factoryCollection = $this->createMock(CollectionFactory::class);
        $this->escaper = $this->createMock(Escaper::class);
        $this->dataPersistor = $this->getMockForAbstractClass(DataPersistorInterface::class);

        $objectManager = new ObjectManager($this);
        $objects = [
            [
                SecureHtmlRenderer::class,
                $this->createMock(SecureHtmlRenderer::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);

        $this->element = $objectManager->getObject(
            Subscriptions::class,
            [
                'factoryElement' => $this->factoryElement,
                'factoryCollection' => $this->factoryCollection,
                'escaper' => $this->escaper,
                'dataPersistor' => $this->dataPersistor,
                'data' => []
            ]
        );
    }

    /**
     * Test to Get the Html for the element
     *
     * @param array $data
     * @param array $elementsHtml
     * @param string $expectedHtml
     * @return void
     * @dataProvider getElementHtmlDataProvider
     */
    public function testGetElementHtml(array $data, array $elementsHtml, string $expectedHtml): void
    {
        $this->escaper->method('escapeHtml')->withAnyParameters()->willReturnArgument(0);
        $selectElementId = $data['name'] . '_store_' . $data['subscriptions'][0]['website_id'];
        $selectElement = $this->createMock(AbstractElement::class);
        $selectElement->expects($this->once())->method('setId')->with($selectElementId);
        $selectElement->expects($this->once())->method('setForm')->willReturnSelf();
        $selectElement->method('toHtml')->willReturn($elementsHtml['store']);
        $statusElementId = $data['name'] . '_status_' . $data['subscriptions'][0]['website_id'];
        $statusElement = $this->createMock(AbstractElement::class);
        $statusElement->expects($this->once())->method('setId')->with($statusElementId);
        $statusElement->expects($this->once())->method('setForm')->willReturnSelf();
        $statusElement->method('toHtml')->willReturn($elementsHtml['status']);
        $this->factoryElement->method('create')->willReturnMap(
            [
                [
                    'select',
                    [
                        'data' => [
                            'name' => "{$data['name']}_store[{$data['subscriptions'][0]['website_id']}]",
                            'data-form-part' => $data['target_form'],
                            'values' => $data['subscriptions'][0]['store_options'],
                            'value' => $data['subscriptions'][0]['store_id'],
                            'required' => true,
                        ]
                    ],
                    $selectElement
                ],
                [
                    'checkbox',
                    [
                        'data' => [
                            'name' => "{$data['name']}_status[{$data['subscriptions'][0]['website_id']}]",
                            'data-form-part' => $data['target_form'],
                            'value' => $data['subscriptions'][0]['status'],
                            'onchange' => 'this.value = this.checked;',
                        ]
                    ],
                    $statusElement
                ]
            ]
        );
        $this->dataPersistor->method('get')->willReturn([]);
        $this->element->setData($data);

        $this->assertEquals($expectedHtml, $this->element->getElementHtml());
    }

    /**
     * Data provider for test to get the html
     *
     * @return array
     */
    public static function getElementHtmlDataProvider(): array
    {
        $customerId = 33;
        $elementName = 'element_name';
        $targetForm = 'target_form';
        $websiteId = 1;
        $websiteName = 'Website 1';
        $storeId = 2;
        $status = true;
        $storeOptions = ['array_of_store_options'];
        $lastUpdated = 'last updated';
        $storeElementHtml = 'storeElementHtml';
        $statusElementHtml = 'statusElementHtml';
        $outputHtmlTemplate = "<table class=\"admin__table-secondary\">"
            . "<tr><th>%s</th><th class=\"subscriber-status\">%s</th><th>%s</th><th>%s</th></tr>"
            . "<tr><td>%s</td><td class=\"subscriber-status\">%s</td><td>%s</td><td>%s</td></tr></table>";

        return [
            [
                'data' => [
                    'customer_id' => $customerId,
                    'name' => $elementName,
                    'target_form' => $targetForm,
                    'subscriptions' => [
                        [
                            'store_id' => $storeId,
                            'website_id' => $websiteId,
                            'website_name' => $websiteName,
                            'status' => $status,
                            'store_options' => $storeOptions,
                            'last_updated' => $lastUpdated,
                        ],
                    ],
                ],
                'elementsHtml' => [
                    'status' => $statusElementHtml,
                    'store' => $storeElementHtml,
                ],
                'expectedHtml' => sprintf(
                    $outputHtmlTemplate,
                    'Website',
                    'Subscribed',
                    'Store View',
                    'Last Updated At',
                    $websiteName,
                    $statusElementHtml,
                    $storeElementHtml,
                    $lastUpdated
                )
            ],
        ];
    }
}
