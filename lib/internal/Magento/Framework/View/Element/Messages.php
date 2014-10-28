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
    protected $usedStorageTypes = array();

    /**
     * Grouped message types
     *
     * @var string[]
     */
    protected $messageTypes = array(
        MessageInterface::TYPE_ERROR,
        MessageInterface::TYPE_WARNING,
        MessageInterface::TYPE_NOTICE,
        MessageInterface::TYPE_SUCCESS
    );

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
     * Constructor
     *
     * @param Template\Context $context
     * @param \Magento\Framework\Message\Factory $messageFactory
     * @param \Magento\Framework\Message\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Message\Factory $messageFactory,
        \Magento\Framework\Message\CollectionFactory $collectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = array()
    ) {
        $this->messageFactory = $messageFactory;
        $this->collectionFactory = $collectionFactory;
        $this->messageManager = $messageManager;
        $this->_isScopePrivate = true;
        parent::__construct($context, $data);
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addStorageType($this->messageManager->getDefaultGroup());
        $this->addMessages($this->messageManager->getMessages(true));
        parent::_prepareLayout();
        return $this;
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
     * @return  array
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
     * @param null|string|array|\Magento\Framework\Object &$html
     * @return void
     */
    protected function _dispatchRenderGroupedAfterEvent(&$html)
    {
        $transport = new \Magento\Framework\Object(array('output' => $html));
        $params = array(
            'element_name' => $this->getNameInLayout(),
            'layout' => $this->getLayout(),
            'transport' => $transport
        );
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
                    $html .= '<' . $this->secondLevelTagName . ' class="message ' . $type . '">';
                    $html .= '<' . $this->contentWrapTagName . $this->getUiId('message', $type) . '>';
                    $html .= $message->getText();
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
        return array('storage_types' => serialize($this->usedStorageTypes));
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
