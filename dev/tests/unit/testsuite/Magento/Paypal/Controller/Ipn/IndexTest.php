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

namespace Magento\Paypal\Controller\Ipn;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    /** @var Index */
    protected $model;

    /** @var \Magento\Framework\Logger|\PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $response;

    protected function setUp()
    {
        $this->logger = $this->getMock('Magento\Framework\Logger', [], [], '', false);
        $this->request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            'Magento\Paypal\Controller\Ipn\Index',
            [
                'logger' => $this->logger,
                'request' => $this->request,
                'response' => $this->response,
            ]
        );
    }

    public function testIndexActionException()
    {
        $this->request->expects($this->once())->method('isPost')->will($this->returnValue(true));
        $exception = new \Exception();
        $this->request->expects($this->once())->method('getPost')->will($this->throwException($exception));
        $this->logger->expects($this->once())->method('logException')->with($this->identicalTo($exception));
        $this->response->expects($this->once())->method('setHttpResponseCode')->with(500);
        $this->model->execute();
    }
}
