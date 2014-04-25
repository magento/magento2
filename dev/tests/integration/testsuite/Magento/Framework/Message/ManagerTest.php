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
namespace Magento\Framework\Message;

/**
 * \Magento\Framework\Message\Manager test case
 */
class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Message\Manager
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create('Magento\Framework\Message\Manager');
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testAddMessage()
    {
        $errorMessage = $this->objectManager->create('Magento\Framework\Message\Error', array('text' => 'some text'));
        $this->model->addMessage($errorMessage);

        $customGroup = 'custom-group';
        $errorMessageCustom = $this->objectManager->create(
            'Magento\Framework\Message\Error',
            array('text' => 'some custom group')
        );
        $this->model->addMessage($errorMessageCustom, $customGroup);

        $this->assertEquals($errorMessage, $this->model->getMessages()->getLastAddedMessage());
        $this->assertEquals(
            $errorMessageCustom,
            $this->model->getMessages(false, $customGroup)->getLastAddedMessage()
        );
        $this->assertEquals($errorMessageCustom, $this->model->getMessages(true, $customGroup)->getLastAddedMessage());

        $this->assertEmpty($this->model->getMessages(false, $customGroup)->getItems());
        $this->assertEmpty($this->model->getMessages(false, $customGroup)->getLastAddedMessage());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testAddMessages()
    {
        $customGroup = 'custom-group';
        $messages = array(
            $this->objectManager->create('Magento\Framework\Message\Error', array('text' => 'some text 1')),
            $this->objectManager->create('Magento\Framework\Message\Error', array('text' => 'some text 2')),
            $this->objectManager->create('Magento\Framework\Message\Error', array('text' => 'some text 3')),
            $this->objectManager->create('Magento\Framework\Message\Error', array('text' => 'some text 4'))
        );

        $this->model->addMessages($messages);
        array_shift($messages);
        $this->model->addMessages($messages, $customGroup);
        $this->assertEquals(4, $this->model->getMessages()->getCount());
        $this->assertEquals(3, $this->model->getMessages(false, $customGroup)->getCount());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testAddUniqueMessages()
    {
        $errorMessageFirst = $this->objectManager
            ->create('Magento\Framework\Message\Error', array('text' => 'some text'));
        $errorMessageSecond = $this->objectManager
            ->create('Magento\Framework\Message\Error', array('text' => 'some text'));
        $this->model->addUniqueMessages($errorMessageFirst);
        $this->model->addUniqueMessages($errorMessageSecond);

        $this->assertEquals(1, $this->model->getMessages()->getCount());
        $this->assertEquals(
            $errorMessageFirst->getText(),
            $this->model->getMessages()->getLastAddedMessage()->getText()
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testAddError()
    {
        $customGroup = 'custom-group';
        $this->model->addError('some text');
        $this->model->addError('some text 2', $customGroup);
        $this->assertEquals(1, $this->model->getMessages()->getCount());
        $this->assertEquals(1, $this->model->getMessages()->getCountByType(MessageInterface::TYPE_ERROR));
        $this->assertEquals(0, $this->model->getMessages()->getCountByType(MessageInterface::TYPE_WARNING));
        $this->assertEquals(0, $this->model->getMessages()->getCountByType(MessageInterface::TYPE_NOTICE));
        $this->assertEquals(0, $this->model->getMessages()->getCountByType(MessageInterface::TYPE_SUCCESS));
        $this->assertEquals('some text', $this->model->getMessages()->getLastAddedMessage()->getText());

        $this->assertEquals(1, $this->model->getMessages(false, $customGroup)->getCount());
        $this->assertEquals(
            'some text 2',
            $this->model->getMessages(false, $customGroup)->getLastAddedMessage()->getText()
        );
    }
}
