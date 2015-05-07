<?php
/***
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Data\Test\Unit\Collection;

class FilesystemTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Data\Collection\Filesystem */
    private $model;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject('Magento\Framework\Data\Collection\Filesystem');
    }

    public function testFilterCallbackLike()
    {
        $field = 'field';
        $row = [$field => 'beginning_filter_target_end',];
        $filterValueSuccess = new \Zend_Db_Expr('%filter_target%');
        $filterValueFailure = new \Zend_Db_Expr('%not_found_in_the_row%');

        $this->assertTrue($this->model->filterCallbackLike($field, $filterValueSuccess, $row));
        $this->assertFalse($this->model->filterCallbackLike($field, $filterValueFailure, $row));
    }
}
