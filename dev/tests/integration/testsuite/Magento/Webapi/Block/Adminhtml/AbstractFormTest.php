<?php
/**
 * Abstract test case for Webapi forms. It was introduced to avoid copy-paste in form tests.
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Webapi\Block\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
abstract class AbstractFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Form class must be defined in children.
     *
     * @var string
     */
    protected $_formClass = '';

    /**
     * @var \Magento\Webapi\Block\Adminhtml\User\Edit\Form
     */
    protected $_block;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Backend\Model\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Magento\Core\Model\BlockFactory
     */
    protected $_blockFactory;

    protected function setUp()
    {
        parent::setUp();
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_urlBuilder = $this->getMockBuilder('Magento\Backend\Model\Url')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_layout = $this->_objectManager->get('Magento\View\LayoutInterface');
        $this->_blockFactory = $this->_objectManager->get('Magento\Core\Model\BlockFactory');
        $this->_block = $this->_blockFactory->createBlock($this->_formClass, array(
            'context' => \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\Backend\Block\Template\Context',
                array('urlBuilder' => $this->_urlBuilder)
            )
        ));
        $this->_layout->addBlock($this->_block);
    }

    protected function tearDown()
    {
        $this->_objectManager->removeSharedInstance('Magento\Core\Model\Layout');
        unset($this->_objectManager, $this->_urlBuilder, $this->_layout, $this->_blockFactory, $this->_block);
    }

    /**
     * Test _prepareForm method.
     */
    public function testPrepareForm()
    {
        // TODO: Move to unit tests after MAGETWO-4015 complete.
        $this->assertEmpty($this->_block->getForm());

        $this->_urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('*/*/save', array())
            ->will($this->returnValue('action_url'));
        $this->_block->toHtml();

        $form = $this->_block->getForm();
        $this->assertInstanceOf('Magento\Data\Form', $form);
        $this->assertTrue($form->getUseContainer());
        $this->assertEquals('edit_form', $form->getId());
        $this->assertEquals('post', $form->getMethod());
        $this->assertEquals('action_url', $form->getAction());
    }
}
