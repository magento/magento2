<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\TestFramework\Helper\ObjectManager;

class ResponseFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\ResponseFactory
     */
    private $factory;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\DocumentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $documentFactory;

    /**
     * @var \Magento\Framework\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->documentFactory = $this->getMockBuilder('Magento\Framework\Search\Adapter\Mysql\DocumentFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = $this->getMockBuilder('\Magento\Framework\ObjectManager')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->factory = $helper->getObject(
            'Magento\Framework\Search\Adapter\Mysql\ResponseFactory',
            ['documentFactory' => $this->documentFactory, 'objectManager' => $this->objectManager]
        );
    }

    public function testCreate()
    {
        $rawResponse = [
            'documents' => [
                ['title' => 'oneTitle', 'description' => 'oneDescription'],
                ['title' => 'twoTitle', 'description' => 'twoDescription']
            ],
            'aggregations' => []
        ];
        $exceptedResponse = [
            'documents' => [
                [
                    ['name' => 'title', 'value' => 'oneTitle'],
                    ['name' => 'description', 'value' => 'oneDescription'],
                ],
                [
                    ['name' => 'title', 'value' => 'twoTitle'],
                    ['name' => 'description', 'value' => 'twoDescription'],
                ]
            ],
            'aggregations' => []
        ];

        $this->documentFactory->expects($this->at(0))->method('create')
            ->with($this->equalTo($exceptedResponse['documents'][0]))
            ->will($this->returnValue('document1'));
        $this->documentFactory->expects($this->at(1))->method('create')
            ->with($exceptedResponse['documents'][1])
            ->will($this->returnValue('document2'));

        $this->objectManager->expects($this->once())->method('create')
            ->with(
                $this->equalTo('\Magento\Framework\Search\QueryResponse'),
                $this->equalTo(['documents' => ['document1', 'document2'], 'aggregations' => null])
            )
            ->will($this->returnValue('QueryResponseObject'));

        $result = $this->factory->create($rawResponse);
        $this->assertEquals('QueryResponseObject', $result);
    }
}
