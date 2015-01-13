<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ObjectManager\Relations;

class CompiledTest extends \PHPUnit_Framework_TestCase
{
    public function testHas()
    {
        $relations = ['amazing' => 'yes'];

        $model = new \Magento\Framework\ObjectManager\Relations\Compiled($relations);
        $this->assertEquals(true, $model->has('amazing'));
        $this->assertEquals(false, $model->has('fuzzy'));
    }

    public function testGetParents()
    {
        $relations = ['amazing' => 'parents'];

        $model = new \Magento\Framework\ObjectManager\Relations\Compiled($relations);
        $this->assertEquals('parents', $model->getParents('amazing'));
    }
}
