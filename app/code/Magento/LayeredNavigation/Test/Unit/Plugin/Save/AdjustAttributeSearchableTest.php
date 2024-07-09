<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\LayeredNavigation\Test\Unit\Plugin\Save;

use Magento\Catalog\Model\Product\Attribute\Frontend\Inputtype\Presentation;
use Magento\LayeredNavigation\Plugin\Save\AdjustAttributeSearchable;
use PHPUnit\Framework\TestCase;

class AdjustAttributeSearchableTest extends TestCase
{
    /**
     * @return void
     */
    public function testAfterConvertPresentationDataToInputType(): void
    {
        $presentation = $this->createMock(Presentation::class);
        $result = [
            'is_filterable_in_search' => '1',
            'is_searchable' => '0'
        ];
        $interceptor = new AdjustAttributeSearchable();
        $this->assertSame(
            ['is_filterable_in_search' => '0', 'is_searchable' => '0'],
            $interceptor->afterConvertPresentationDataToInputType($presentation, $result)
        );
    }
}
