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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Review\Controller\Adminhtml\Product;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class PostTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Review\Controller\Adminhtml\Product
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerHelper;


    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_registryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_messageManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerInterfaceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeModelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_reviewModelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_reviewFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_ratingModelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_ratingFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceReviewMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperMock;


    protected function setUp()
    {
        $this->_prepareMockObjects();

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_model = $objectManagerHelper->getObject(
            'Magento\Review\Controller\Adminhtml\Product\Post',
            [
                'coreRegistry' => $this->_registryMock,
                'reviewFactory' => $this->_reviewFactoryMock,
                'ratingFactory' => $this->_ratingFactoryMock,
                'request' => $this->_requestMock,
                'response' => $this->_responseMock,
                'objectManager' => $this->_objectManagerMock,
                'messageManager' => $this->_messageManagerMock,
                'helper' => $this->_helperMock
            ]
        );

    }

    /**
     * Get mock objects for SetUp()
     */
    protected function _prepareMockObjects()
    {
        $requestMethods = array(
            'getPost',
            'getModuleName',
            'setModuleName',
            'getActionName',
            'setActionName',
            'getParam',
            'getCookie'
        );
        $this->_registryMock = $this->getMock('Magento\Framework\Registry', array(), array(), '', false);
        $this->_requestMock = $this->getMock(
            '\Magento\Framework\App\RequestInterface', $requestMethods
        );
        $this->_responseMock = $this->getMock(
            '\Magento\Framework\App\ResponseInterface', array('setRedirect', 'sendResponse')
        );
        $this->_objectManagerMock = $this->getMock(
            '\Magento\Framework\ObjectManager', array('get', 'create', 'configure'), array(), '', false
        );
        $this->_messageManagerMock = $this->getMock('\Magento\Framework\Message\Manager', array(), array(), '', false);
        $this->_storeManagerInterfaceMock = $this->getMockForAbstractClass('Magento\Framework\StoreManagerInterface');
        $this->_storeModelMock = $this->getMock(
            'Magento\Store\Model\Store', array('__wakeup', 'getId'), array(), '', false
        );
        $this->_reviewModelMock = $this->getMock(
            'Magento\Review\Model\Review',
            array('__wakeup', 'create', 'save', 'getId', 'getResource', 'aggregate'),
            array(),
            '',
            false
        );

        $this->_reviewFactoryMock = $this->getMock(
            'Magento\Review\Model\ReviewFactory',
            array('create'),
            array(),
            '',
            false
        );

        $this->_ratingModelMock = $this->getMock(
            'Magento\Review\Model\Rating',
            array('__wakeup', 'setRatingId', 'setReviewId', 'addOptionVote'),
            array(),
            '',
            false);

        $this->_ratingFactoryMock = $this->getMock(
            'Magento\Review\Model\RatingFactory',
            array('create'),
            array(),
            '',
            false
        );

        $this->_helperMock = $this->getMock('\Magento\Backend\Helper\Data', array(), array(), '', false);
    }

    /**
     * Check postAction method and assert that review model storeId equals null.
     */
    public function testPostAction()
    {
        $this->_requestMock->expects($this->at(0))->method('getParam')
            ->will($this->returnValue(1));
        $this->_requestMock->expects($this->at(2))->method('getParam')
            ->will($this->returnValue(array('1' => '1')));
        $this->_requestMock->expects($this->once())->method('getPost')
            ->will($this->returnValue(array('status_id' => 1)));
        $this->_objectManagerMock->expects($this->at(0))->method('get')
            ->with('Magento\Framework\StoreManagerInterface')
            ->will($this->returnValue($this->_storeManagerInterfaceMock));
        $this->_reviewFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->_reviewModelMock));
        $this->_ratingFactoryMock->expects($this->once())->method('create')
            ->will($this->returnValue($this->_ratingModelMock));
        $this->_storeManagerInterfaceMock->expects($this->once())->method('hasSingleStore')
            ->will($this->returnValue(true));
        $this->_storeManagerInterfaceMock->expects($this->once())->method('getStore')
            ->will($this->returnValue($this->_storeModelMock));
        $this->_storeModelMock->expects($this->once())->method('getId')
            ->will($this->returnValue(1));
        $this->_reviewModelMock->expects($this->once())->method('save')
            ->will($this->returnValue($this->_reviewModelMock));
        $this->_reviewModelMock->expects($this->once())->method('getId')
            ->will($this->returnValue(1));
        $this->_reviewModelMock->expects($this->once())->method('aggregate')
            ->will($this->returnValue($this->_reviewModelMock));
        $this->_ratingModelMock->expects($this->once())->method('setRatingId')
            ->will($this->returnSelf());
        $this->_ratingModelMock->expects($this->once())->method('setReviewId')
            ->will($this->returnSelf());
        $this->_ratingModelMock->expects($this->once())->method('addOptionVote')
            ->will($this->returnSelf());
        $this->_helperMock->expects($this->once())->method('geturl')
            ->will($this->returnValue('url'));

        $this->_model->execute();
    }

}
