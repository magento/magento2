<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rule\Test\Unit\Model\Condition\Sql;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ExpressionTest extends \PHPUnit_Framework_TestCase
{
    public function testExpression()
    {
        $expression = (new ObjectManagerHelper($this))->getObject(
            '\Magento\Rule\Model\Condition\Sql\Expression',
            ['expression' => 'SQL_EXPRESSION']
        );
        $this->assertEquals('(SQL_EXPRESSION)', (string)$expression);
    }
}
