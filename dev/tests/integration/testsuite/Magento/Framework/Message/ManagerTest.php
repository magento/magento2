<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Message;

/**
 * \Magento\Framework\Message\Manager test case
 */
class ManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Message\Manager
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(\Magento\Framework\Message\Manager::class);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testAddMessage()
    {
        $errorMessage = $this->objectManager->create(\Magento\Framework\Message\Error::class, ['text' => 'some text']);
        $this->model->addMessage($errorMessage);

        $customGroup = 'custom-group';
        $errorMessageCustom = $this->objectManager->create(
            \Magento\Framework\Message\Error::class,
            ['text' => 'some custom group']
        );
        $this->model->addMessage($errorMessageCustom, $customGroup);

        $this->assertSame($errorMessage, $this->model->getMessages()->getLastAddedMessage());
        $this->assertSame(
            $errorMessageCustom,
            $this->model->getMessages(false, $customGroup)->getLastAddedMessage()
        );
        $this->assertSame($errorMessageCustom, $this->model->getMessages(true, $customGroup)->getLastAddedMessage());

        $this->assertEmpty($this->model->getMessages(false, $customGroup)->getItems());
        $this->assertEmpty($this->model->getMessages(false, $customGroup)->getLastAddedMessage());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testAddMessages()
    {
        $customGroup = 'custom-group';
        $messages = [
            $this->objectManager->create(\Magento\Framework\Message\Error::class, ['text' => 'some text 1']),
            $this->objectManager->create(\Magento\Framework\Message\Error::class, ['text' => 'some text 2']),
            $this->objectManager->create(\Magento\Framework\Message\Error::class, ['text' => 'some text 3']),
            $this->objectManager->create(\Magento\Framework\Message\Error::class, ['text' => 'some text 4']),
        ];

        $this->model->addMessages($messages);
        array_shift($messages);
        $this->model->addMessages($messages, $customGroup);
        $this->assertSame(4, $this->model->getMessages()->getCount());
        $this->assertSame(3, $this->model->getMessages(false, $customGroup)->getCount());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testAddUniqueMessages()
    {
        $errorMessageFirst = $this->objectManager
            ->create(\Magento\Framework\Message\Error::class, ['text' => 'some text']);
        $errorMessageSecond = $this->objectManager
            ->create(\Magento\Framework\Message\Error::class, ['text' => 'some text']);
        $this->model->addUniqueMessages([$errorMessageFirst]);
        $this->model->addUniqueMessages([$errorMessageSecond]);

        $this->assertSame(1, $this->model->getMessages()->getCount());
        $this->assertSame(
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
        $this->assertSame(1, $this->model->getMessages()->getCount());
        $this->assertSame(1, $this->model->getMessages()->getCountByType(MessageInterface::TYPE_ERROR));
        $this->assertSame(0, $this->model->getMessages()->getCountByType(MessageInterface::TYPE_WARNING));
        $this->assertSame(0, $this->model->getMessages()->getCountByType(MessageInterface::TYPE_NOTICE));
        $this->assertSame(0, $this->model->getMessages()->getCountByType(MessageInterface::TYPE_SUCCESS));
        $this->assertSame('some text', $this->model->getMessages()->getLastAddedMessage()->getText());

        $this->assertSame(1, $this->model->getMessages(false, $customGroup)->getCount());
        $this->assertSame(
            'some text 2',
            $this->model->getMessages(false, $customGroup)->getLastAddedMessage()->getText()
        );
    }
}
