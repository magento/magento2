<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Test\Unit\Model;

class SynonymGroupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Search\Model\SynonymGroup
     */
    private $model;

    public function setUp()
    {
        $this->model = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(\Magento\Search\Model\SynonymGroup::class);
    }

    public function testSetGetStoreId()
    {
        $this->assertEquals(0, $this->model->getStoreId());
        $this->assertEquals($this->model, $this->model->setStoreId(1));
        $this->assertEquals(1, $this->model->getStoreId());
    }

    public function testSetGetWebsiteId()
    {
        $this->assertEquals(0, $this->model->getWebsiteId());
        $this->assertEquals($this->model, $this->model->setWebsiteId(1));
        $this->assertEquals(1, $this->model->getWebsiteId());
    }

    public function testSetGetSynonymGroup()
    {
        $this->assertEquals($this->model, $this->model->setSynonymGroup('a,b,c'));
        $this->assertEquals('a,b,c', $this->model->getSynonymGroup());
    }

    public function testSetGetGroupId()
    {
        $this->assertEquals($this->model, $this->model->setGroupId(1));
        $this->assertEquals(1, $this->model->getGroupId());
    }
}
