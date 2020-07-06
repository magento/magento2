<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Related;

class RelatedTest extends AbstractModifierTest
{
    /**
     * @return Related
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(Related::class, [
            'locator' => $this->locatorMock,
        ]);
    }

    /**
     * @return void
     */
    public function testModifyMeta()
    {
        $this->assertArrayHasKey(Related::DATA_SCOPE_RELATED, $this->getModel()->modifyMeta([]));
    }

    /**
     * @return void
     */
    public function testModifyData()
    {
        $data = $this->getSampleData();

        $this->assertSame($data, $this->getModel()->modifyData($data));
    }
}
