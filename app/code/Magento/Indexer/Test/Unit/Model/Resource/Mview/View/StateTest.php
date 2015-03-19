<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Model\Resource\Mview\View;

class StateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Indexer\Model\Resource\Mview\View\State
     */
    protected $model;

    public function testConstruct()
    {
        $resourceMock = $this->getMock(
            '\Magento\Framework\App\Resource',
            [],
            [],
            '',
            false
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $arguments = $objectManager->getConstructArguments(
            '\Magento\Indexer\Model\Resource\Mview\View\State',
            ['resource' => $resourceMock]
        );
        $this->model = $objectManager->getObject(
            '\Magento\Indexer\Model\Resource\Mview\View\State',
            $arguments
        );
        $this->assertEquals(
            [['field' => ['view_id'], 'title' => __('State for the same view')]],
            $this->model->getUniqueFields()
        );
    }
}
