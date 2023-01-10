<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\Adminhtml\Grid\Renderer;

use Magento\Framework\DataObject;

/**
 * Class checks multiaction block rendering with simple product and simple product with options.
 *
 * @see \Magento\Customer\Block\Adminhtml\Grid\Renderer\Multiaction
 */
class MultiactionTest extends AbstractMultiactionTest
{
    /**
     * @dataProvider renderEmptyProvider
     * @param array $columnData
     * @return void
     */
    public function testRenderEmpty(array $columnData): void
    {
        /** @var DataObject $row */
        $row = $this->objectManager->create(DataObject::class);
        $this->blockColumn->addData($columnData);
        $this->blockMultiaction->setColumn($this->blockColumn);
        $this->assertEquals(
            '&nbsp;',
            $this->blockMultiaction->render($row)
        );
    }

    /**
     * Data provider for testRenderEmpty
     *
     * @return array
     */
    public function renderEmptyProvider(): array
    {
        return [
            'empty_actions' => [
                'column_data' => ['actions' => []],
            ],
            'not_array_actions' => [
                'column_data' => ['actions' => 'actions'],
            ],
            'empty_actions_element' => [
                'column_data' => [
                    'actions' => [
                        'action_1' => 'actions',
                    ],
                ],
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/customer_quote_with_items_simple_product_options.php
     * @return void
     */
    public function testRenderProductOptions(): void
    {
        $this->processRender();
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @return void
     */
    public function testRenderSimpleProduct(): void
    {
        $this->markTestSkipped('Test is blocked by issue MC-34612');
        $this->processRender();
    }
}
