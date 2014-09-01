<?php
/**
 * Test class for /Magento/UrlRewrite/Model/UrlRewrite
 *
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

namespace Magento\UrlRewrite\Model;

use Magento\TestFramework\Helper\ObjectManager as ObjectManager;
use Magento\UrlRewrite\Model\UrlRewrite as UrlRewrite;

class UrlRewriteTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\UrlRewrite\Model\UrlRewrite
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;
    
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    private $objectManager;

    public function setUp()
    {

        $resourceMethods = ['getIdFieldName', 'loadByRequestPath', 'load',];
        $this->resourceMock = $this->getMockForAbstractClass('\Magento\Framework\Model\Resource\AbstractResource',
            [], '', false, true, true, $resourceMethods

        );

        $this->objectManager= new ObjectManager($this);

        $this->model = $this->objectManager->getObject('\Magento\UrlRewrite\Model\UrlRewrite',
        [
            'resource' => $this->resourceMock,
        ]
        );
    }

    public function testLoadByRequestPath()
    {
        $path = 'path';

        $this->resourceMock->expects($this->once())
            ->method('loadByRequestPath')
            ->with($this->model, $path);

        $this->model->loadByRequestPath($path);

    }

    public function testLoadByIdPath()
    {
        $path = 'path';

        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($this->model, $path, UrlRewrite::PATH_FIELD);

        $this->model->loadByIdPath($path);
    }

    public function testHasOption()
    {
        $searchOption = 'option2';
        $options='option1,' . $searchOption . ',option3';
        $this->assertTrue($this->model->setOptions($options)->hasOption('option2'));
    }

    public function testGetStoreId()
    {
        $id = 42;
        $this->assertEquals($id, $this->model->setStoreId($id)->getStoreId());
    }
} 