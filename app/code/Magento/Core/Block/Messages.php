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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Messages block
 *
 * @category   Magento
 * @package    Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Block;

class Messages extends \Magento\Core\Block\Template
{
    /**
     * Messages collection
     *
     * @var \Magento\Core\Model\Message\Collection
     */
    protected $_messages;

    /**
     * Store first level html tag name for messages html output
     *
     * @var string
     */
    protected $_messagesFirstLevelTagName = 'ul';

    /**
     * Store second level html tag name for messages html output
     *
     * @var string
     */
    protected $_messagesSecondLevelTagName = 'li';

    /**
     * Store content wrapper html tag name for messages html output
     *
     * @var string
     */
    protected $_messagesContentWrapperTagName = 'span';

    /**
     * Flag which require message text escape
     *
     * @var bool
     */
    protected $_escapeMessageFlag = false;

    /**
     * Storage for used types of message storages
     *
     * @var array
     */
    protected $_usedStorageTypes = array();

    /**
     * Grouped message types
     *
     * @var array
     */
    protected $_messageTypes = array(
        \Magento\Core\Model\Message::ERROR,
        \Magento\Core\Model\Message::WARNING,
        \Magento\Core\Model\Message::NOTICE,
        \Magento\Core\Model\Message::SUCCESS
    );

    /**
     * Message singleton
     *
     * @var \Magento\Core\Model\Message
     */
    protected $_message;

    /**
     * Message model factory
     *
     * @var \Magento\Core\Model\MessageFactory
     */
    protected $_messageFactory;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param \Magento\Core\Model\Message $message
     * @param \Magento\Core\Model\Message\CollectionFactory $messageFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        \Magento\Core\Model\Message $message,
        \Magento\Core\Model\Message\CollectionFactory $messageFactory,
        array $data = array()
    ) {
        $this->_message = $message;
        $this->_messageFactory = $messageFactory;
        parent::__construct($coreData, $context, $data);
    }

    /**
     * Preparing global layout
     *
     * @return \Magento\Core\Block\Messages
     */
    protected function _prepareLayout()
    {
        $this->addStorageType(get_class($this->_session));
        $this->addMessages($this->_session->getMessages(true));
        parent::_prepareLayout();
        return $this;
    }

    /**
     * Set message escape flag
     *
     * @param bool $flag
     * @return \Magento\Core\Block\Messages
     */
    public function setEscapeMessageFlag($flag)
    {
        $this->_escapeMessageFlag = $flag;
        return $this;
    }

    /**
     * Set messages collection
     *
     * @param   \Magento\Core\Model\Message\Collection $messages
     * @return  \Magento\Core\Block\Messages
     */
    public function setMessages(\Magento\Core\Model\Message\Collection $messages)
    {
        $this->_messages = $messages;
        return $this;
    }

    /**
     * Add messages to display
     *
     * @param \Magento\Core\Model\Message\Collection $messages
     * @return \Magento\Core\Block\Messages
     */
    public function addMessages(\Magento\Core\Model\Message\Collection $messages)
    {
        foreach ($messages->getItems() as $message) {
            $this->getMessageCollection()->add($message);
        }
        return $this;
    }

    /**
     * Retrieve messages collection
     *
     * @return \Magento\Core\Model\Message\Collection
     */
    public function getMessageCollection()
    {
        if (!($this->_messages instanceof \Magento\Core\Model\Message\Collection)) {
            $this->_messages = $this->_messageFactory->create();
        }
        return $this->_messages;
    }

    /**
     * Adding new message to message collection
     *
     * @param   \Magento\Core\Model\Message\AbstractMessage $message
     * @return  \Magento\Core\Block\Messages
     */
    public function addMessage(\Magento\Core\Model\Message\AbstractMessage $message)
    {
        $this->getMessageCollection()->add($message);
        return $this;
    }

    /**
     * Adding new error message
     *
     * @param   string $message
     * @return  \Magento\Core\Block\Messages
     */
    public function addError($message)
    {
        $this->addMessage($this->_message->error($message));
        return $this;
    }

    /**
     * Adding new warning message
     *
     * @param   string $message
     * @return  \Magento\Core\Block\Messages
     */
    public function addWarning($message)
    {
        $this->addMessage($this->_message->warning($message));
        return $this;
    }

    /**
     * Adding new notice message
     *
     * @param   string $message
     * @return  \Magento\Core\Block\Messages
     */
    public function addNotice($message)
    {
        $this->addMessage($this->_message->notice($message));
        return $this;
    }

    /**
     * Adding new success message
     *
     * @param   string $message
     * @return  \Magento\Core\Block\Messages
     */
    public function addSuccess($message)
    {
        $this->addMessage($this->_message->success($message));
        return $this;
    }

    /**
     * Retrieve messages array by message type
     *
     * @param   string $type
     * @return  array
     */
    public function getMessages($type=null)
    {
        return $this->getMessageCollection()->getItems($type);
    }

    /**
     * Retrieve messages in HTML format
     *
     * @param   string $type
     * @return  string
     */
    public function getHtml($type = null)
    {
        $html = '<' . $this->_messagesFirstLevelTagName . ' id="admin_messages">';
        foreach ($this->getMessages($type) as $message) {
            $html .= '<' . $this->_messagesSecondLevelTagName . ' class="' . $message->getType() . '-msg" '
                . $this->getUiId('message') . '>'
                . $this->_escapeMessageFlag ? $this->escapeHtml($message->getText()) : $message->getText()
                . '</' . $this->_messagesSecondLevelTagName . '>';
        }
        $html .= '</' . $this->_messagesFirstLevelTagName . '>';
        return $html;
    }

    /**
     * Return grouped message types
     *
     * @return array
     */
    public function getMessageTypes()
    {
        return $this->_messageTypes;
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
     * @param $html
     */
    protected function _dispatchRenderGroupedAfterEvent(&$html)
    {
        $transport = new \Magento\Object(array('output' => $html));
        $params = array(
            'element_name' => $this->getNameInLayout(),
            'layout'       => $this->getLayout(),
            'transport'    => $transport,
        );
        $this->_eventManager->dispatch('core_message_block_render_grouped_html_after', $params);
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
            if ($messages = $this->getMessages($type)) {
                if (!$html) {
                    $html .= '<' . $this->_messagesFirstLevelTagName . ' class="messages">';
                }
                $html .= '<' . $this->_messagesSecondLevelTagName . ' class="' . $type . '-msg">';
                $html .= '<' . $this->_messagesFirstLevelTagName . '>';

                foreach ($messages as $message) {
                    $html.= '<' . $this->_messagesSecondLevelTagName . '>';
                    $html.= '<' . $this->_messagesContentWrapperTagName .  $this->getUiId('message', $type) .  '>';
                    $html.= ($this->_escapeMessageFlag) ? $this->escapeHtml($message->getText()) : $message->getText();
                    $html.= '</' . $this->_messagesContentWrapperTagName . '>';
                    $html.= '</' . $this->_messagesSecondLevelTagName . '>';
                }
                $html .= '</' . $this->_messagesFirstLevelTagName . '>';
                $html .= '</' . $this->_messagesSecondLevelTagName . '>';
            }
        }
        if ($html) {
            $html .= '</' . $this->_messagesFirstLevelTagName . '>';
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
     */
    public function setMessagesFirstLevelTagName($tagName)
    {
        $this->_messagesFirstLevelTagName = $tagName;
    }

    /**
     * Set messages first level html tag name for output messages as html
     *
     * @param string $tagName
     */
    public function setMessagesSecondLevelTagName($tagName)
    {
        $this->_messagesSecondLevelTagName = $tagName;
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array(
            'storage_types' => serialize($this->_usedStorageTypes)
        );
    }

    /**
     * Add used storage type
     *
     * @param string $type
     */
    public function addStorageType($type)
    {
        $this->_usedStorageTypes[] = $type;
    }

    /**
     * Whether or not to escape the message.
     *
     * @return boolean
     */
    public function shouldEscapeMessage()
    {
        return $this->_escapeMessageFlag;
    }
}
