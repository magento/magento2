<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Group;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Filesystem;
use PHPUnit_Framework_MockObject_MockObject as Mock;

class GroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Group
     */
    private $model;

    /**
     * @var Http|Mock
     */
    private $requestMock;

    /**
     * @var Filesystem|Mock
     */
    private $filesystemMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystemMock = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = (new ObjectManager($this))->getObject(
            Group::class,
            [
                'filesystem' => $this->filesystemMock
            ]
        );
    }

    public function testSetCode()
    {
        $this->assertSame($this->model, $this->model->setCode('code'));
    }

    public function testGetCode()
    {
        $this->model->setCode('some_code');

        $this->assertSame('some_code', $this->model->getCode());
    }
}
