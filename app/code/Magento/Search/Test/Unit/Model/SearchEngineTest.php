<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SearchEngineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Search\Model\SearchEngine
     */
    private $searchEngine;

    /**
     * @var \Magento\Framework\Search\AdapterInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $adapterFactory = $this->getMockBuilder('Magento\Search\Model\AdapterFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapter = $this->getMockBuilder('Magento\Framework\Search\AdapterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $adapterFactory->expects($this->once())->method('create')->will($this->returnValue($this->adapter));

        $this->searchEngine = $helper->getObject(
            'Magento\Search\Model\SearchEngine',
            [
                'adapterFactory' => $adapterFactory,
            ]
        );
    }

    public function testSearch()
    {
        $request = $this->getMockBuilder('Magento\Framework\Search\RequestInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $response = $this->getMockBuilder('Magento\Framework\Search\ResponseInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapter->expects($this->once())
            ->method('query')
            ->with($this->equalTo($request))
            ->will($this->returnValue($response));

        $result = $this->searchEngine->search($request);
        $this->assertInstanceOf('Magento\Framework\Search\ResponseInterface', $result);
    }
}
