<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Ui\Component\Listing\Columns;

use Magento\Catalog\Test\Unit\Ui\Component\Listing\Columns\AbstractColumnTest;
use Magento\Review\Ui\Component\Listing\Columns\ReviewActions;

class ReviewActionsTest extends AbstractColumnTest
{
    /**
     * {@inheritdoc}
     */
    protected function getModel()
    {
        return $this->objectManager->getObject(ReviewActions::class, [
            'context' => $this->contextMock,
            'uiComponentFactory' => $this->uiComponentFactoryMock,
            'components' => [],
            'data' => [],
        ]);
    }

    public function testPrepareDataSourceToBeEmpty()
    {
        $this->assertSame([], $this->getModel()->prepareDataSource([]));
    }

    public function testPrepareDataSource()
    {
        $this->assertArrayHasKey('data', $this->getModel()->prepareDataSource(['data' => ['items' => []]]));
    }
}
