<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element;

use Magento\Framework\Message\MessageInterface;

/**
 * Class Messages
 */
class Messages extends Template
{
    /**
     * Messages collection
     *
     * @var \Magento\Framework\Message\Collection
     */
    protected $messages;

    /**
     * Store first level html tag name for messages html output
     *
     * @var string
     */
    protected $firstLevelTagName = 'div';

    /**
     * Store second level html tag name for messages html output
     *
     * @var string
     */
    protected $secondLevelTagName = 'div';

    /**
     * Store content wrapper html tag name for messages html output
     *
     * @var string
     */
    protected $contentWrapTagName = 'div';

    /**
     * Storage for used types of message storages
     *
     * @var array
     */
    protected $usedStorageTypes = [];

    /**
     * Grouped message types
     *
     * @var string[]
     */
    protected $messageTypes = [
        MessageInterface::TYPE_ERROR,
        MessageInterface::TYPE_WARNING,
        MessageInterface::TYPE_NOTICE,
        MessageInterface::TYPE_SUCCESS,
    ];

    /**
     * Message singleton
     *
     * @var \Magento\Framework\Message\Factory
     */
    protected $messageFactory;

    /**
     * Message model factory
     *
     * @var \Magento\Framework\Message\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Message manager
     *
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Message\InterpretationStrategyInterface
     */
    private $interpretationStrategy;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param \Magento\Framework\Message\Factory $messageFactory
     * @param \Magento\Framework\Message\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param Message\InterpretationStrategyInterface $interpretationStrategy
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Message\Factory $messageFactory,
        \Magento\Framework\Message\CollectionFactory $collectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Message\InterpretationStrategyInterface $interpretationStrategy,
        array $data = []
    ) {
        $this->messageFactory = $messageFactory;
        $this->collectionFactory = $collectionFactory;
        $this->messageManager = $messageManager;
        parent::__construct($context, $data);
        $this->interpretationStrategy = $interpretationStrategy;
    }

    /**
     * Set messages collection
     *
     * @param   \Magento\Framework\Message\Collection $messages
     * @return  $this
     */
    public function setMessages(\Magento\Framework\Message\Collection $messages)
    {
        $this->messages = $messages;
        return $this;
    }

    /**
     * Add messages to display
     *
     * @param \Magento\Framework\Message\Collection $messages
     * @return $this
     */
    public function addMessages(\Magento\Framework\Message\Collection $messages)
    {
        foreach ($messages->getItems() as $message) {
            $this->getMessageCollection()->addMessage($message);
        }
        return $this;
    }

    /**
     * Retrieve messages collection
     *
     * @return \Magento\Framework\Message\Collection
     */
    public function getMessageCollection()
    {
        if (!$this->messages instanceof \Magento\Framework\Message\Collection) {
            $this->messages = $this->collectionFactory->create();
        }
        return $this->messages;
    }

    /**
     * Adding new message to message collection
     *
     * @param MessageInterface $message
     * @return $this
     */
    public function addMessage(MessageInterface $message)
    {
        $this->getMessageCollection()->addMessage($message);
        return $this;
    }

    /**
     * Adding new error message
     *
     * @param   string $message
     * @return  $this
     */
    public function addError($message)
    {
        $this->addMessage($this->messageFactory->create(MessageInterface::TYPE_ERROR, $message));
        return $this;
    }

    /**
     * Adding new warning message
     *
     * @param   string $message
     * @return  $this
     */
    public function addWarning($message)
    {
        $this->addMessage($this->messageFactory->create(MessageInterface::TYPE_WARNING, $message));
        return $this;
    }

    /**
     * Adding new notice message
     *
     * @param   string $message
     * @return  $this
     */
    public function addNotice($message)
    {
        $this->addMessage($this->messageFactory->create(MessageInterface::TYPE_NOTICE, $message));
        return $this;
    }

    /**
     * Adding new success message
     *
     * @param   string $message
     * @return  $this
     */
    public function addSuccess($message)
    {
        $this->addMessage($this->messageFactory->create(MessageInterface::TYPE_SUCCESS, $message));
        return $this;
    }

    /**
     * Retrieve messages array by message type
     *
     * @param   string $type
     * @return  MessageInterface[]
     */
    public function getMessagesByType($type)
    {
        return $this->getMessageCollection()->getItemsByType($type);
    }

    /**
     * Return grouped message types
     *
     * @return array
     */
    public function getMessageTypes()
    {
        return $this->messageTypes;
    }

    /**
     * Retrieve messages in HTML format grouped by type
     *
     * @return string
     */
    public function getGroupedHtml()
    {
        $html = $this->_renderMessagesByType();
        $this->_dispatchRenderGroupedAfterEvent($html);
        return $html;
    }

    /**
     * Dispatch render after event
     *
     * @param null|string|array|\Magento\Framework\DataObject &$html
     * @return void
     */
    protected function _dispatchRenderGroupedAfterEvent(&$html)
    {
        $transport = new \Magento\Framework\DataObject(['output' => $html]);
        $params = [
            'element_name' => $this->getNameInLayout(),
            'layout' => $this->getLayout(),
            'transport' => $transport,
        ];
        $this->_eventManager->dispatch('view_message_block_render_grouped_html_after', $params);
        $html = $transport->getData('output');
    }

    /**
     * Render messages in HTML format grouped by type
     *
     * @return string
     */
    protected function _renderMessagesByType()
    {
        $html = '';
        foreach ($this->getMessageTypes() as $type) {
            if ($messages = $this->getMessagesByType($type)) {
                if (!$html) {
                    $html .= '<' . $this->firstLevelTagName . ' class="messages">';
                }

                foreach ($messages as $message) {
                    $html .= '<' . $this->secondLevelTagName . ' class="message ' . 'message-' . $type . ' ' . $type .
                        '">';
                    $html .= '<' . $this->contentWrapTagName . $this->getUiId('message', $type) . '>';
                    $html .= $this->interpretationStrategy->interpret($message);
                    $html .= '</' . $this->contentWrapTagName . '>';
                    $html .= '</' . $this->secondLevelTagName . '>';
                }
            }
        }
        if ($html) {
            $html .= '</' . $this->firstLevelTagName . '>';
        }
        return $html;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->getTemplate()) {
            $html = parent::_toHtml();
        } else {
            $html = $this->_renderMessagesByType();
        }
        return $html;
    }

    /**
     * Set messages first level html tag name for output messages as html
     *
     * @param string $tagName
     * @return void
     */
    public function setFirstLevelTagName($tagName)
    {
        $this->firstLevelTagName = $tagName;
    }

    /**
     * Set messages first level html tag name for output messages as html
     *
     * @param string $tagName
     * @return void
     */
    public function setSecondLevelTagName($tagName)
    {
        $this->secondLevelTagName = $tagName;
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return ['storage_types' => serialize($this->usedStorageTypes)];
    }

    /**
     * Add used storage type
     *
     * @param string $type
     * @return void
     */
    public function addStorageType($type)
    {
        $this->usedStorageTypes[] = $type;
    }
}
