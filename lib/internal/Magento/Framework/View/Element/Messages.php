<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element;

use Magento\Framework\Message\MessageInterface;

/**
 * Class Messages
 *
 * @api
 * @since 2.0.0
 */
class Messages extends Template
{
    /**
     * Messages collection
     *
     * @var \Magento\Framework\Message\Collection
     * @since 2.0.0
     */
    protected $messages;

    /**
     * Store first level html tag name for messages html output
     *
     * @var string
     * @since 2.0.0
     */
    protected $firstLevelTagName = 'div';

    /**
     * Store second level html tag name for messages html output
     *
     * @var string
     * @since 2.0.0
     */
    protected $secondLevelTagName = 'div';

    /**
     * Store content wrapper html tag name for messages html output
     *
     * @var string
     * @since 2.0.0
     */
    protected $contentWrapTagName = 'div';

    /**
     * Storage for used types of message storages
     *
     * @var array
     * @since 2.0.0
     */
    protected $usedStorageTypes = [];

    /**
     * Grouped message types
     *
     * @var string[]
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected $messageFactory;

    /**
     * Message model factory
     *
     * @var \Magento\Framework\Message\CollectionFactory
     * @since 2.0.0
     */
    protected $collectionFactory;

    /**
     * Message manager
     *
     * @var \Magento\Framework\Message\ManagerInterface
     * @since 2.0.0
     */
    protected $messageManager;

    /**
     * @var Message\InterpretationStrategyInterface
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getMessagesByType($type)
    {
        return $this->getMessageCollection()->getItemsByType($type);
    }

    /**
     * Return grouped message types
     *
     * @return array
     * @since 2.0.0
     */
    public function getMessageTypes()
    {
        return $this->messageTypes;
    }

    /**
     * Retrieve messages in HTML format grouped by type
     *
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setSecondLevelTagName($tagName)
    {
        $this->secondLevelTagName = $tagName;
    }

    /**
     * Get cache key informative items
     *
     * @return array
     * @since 2.0.0
     */
    public function getCacheKeyInfo()
    {
        return ['storage_types' => implode('|', $this->usedStorageTypes)];
    }

    /**
     * Add used storage type
     *
     * @param string $type
     * @return void
     * @since 2.0.0
     */
    public function addStorageType($type)
    {
        $this->usedStorageTypes[] = $type;
    }
}
