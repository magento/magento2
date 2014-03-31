<?php
/**
 * Mail Template Transport Builder
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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Mail\Template;

class TransportBuilder
{
    /**
     * Template Identifier
     *
     * @var string
     */
    protected $templateIdentifier;

    /**
     * Template Variables
     *
     * @var array
     */
    protected $templateVars;

    /**
     * Template Options
     *
     * @var array
     */
    protected $templateOptions;

    /**
     * Mail Transport
     *
     * @var \Magento\Mail\TransportInterface
     */
    protected $transport;

    /**
     * Template Factory
     *
     * @var FactoryInterface
     */
    protected $templateFactory;

    /**
     * Object Manager
     *
     * @var \Magento\ObjectManager
     */
    protected $objectManager;

    /**
     * Message
     *
     * @var \Magento\Mail\Message
     */
    protected $message;

    /**
     * Sender resolver
     *
     * @var \Magento\Mail\Template\SenderResolverInterface
     */
    protected $_senderResolver;

    /**
     * @param FactoryInterface $templateFactory
     * @param \Magento\Mail\Message $message
     * @param \Magento\Mail\Template\SenderResolverInterface $senderResolver
     * @param \Magento\ObjectManager $objectManager
     */
    public function __construct(
        \Magento\Mail\Template\FactoryInterface $templateFactory,
        \Magento\Mail\Message $message,
        \Magento\Mail\Template\SenderResolverInterface $senderResolver,
        \Magento\ObjectManager $objectManager
    ) {
        $this->templateFactory = $templateFactory;
        $this->message = $message;
        $this->objectManager = $objectManager;
        $this->_senderResolver = $senderResolver;
    }

    /**
     * Add cc address
     *
     * @param array|string $address
     * @param string $name
     * @return $this
     */
    public function addCc($address, $name = '')
    {
        $this->message->addCc($address, $name);
        return $this;
    }

    /**
     * Add to address
     *
     * @param array|string $address
     * @param string $name
     * @return $this
     */
    public function addTo($address, $name = '')
    {
        $this->message->addTo($address, $name);
        return $this;
    }

    /**
     * Add bcc address
     *
     * @param array|string $address
     * @return $this
     */
    public function addBcc($address)
    {
        $this->message->addBcc($address);
        return $this;
    }

    /**
     * Set Reply-To Header
     *
     * @param string $email
     * @param string|null $name
     * @return $this
     */
    public function setReplyTo($email, $name = null)
    {
        $this->message->setReplyTo($email, $name);
        return $this;
    }

    /**
     * Set mail from address
     *
     * @param string|array $from
     * @return $this
     */
    public function setFrom($from)
    {
        $result = $this->_senderResolver->resolve($from);
        $this->message->setFrom($result['email'], $result['name']);
        return $this;
    }

    /**
     * Set template identifier
     *
     * @param string $templateIdentifier
     * @return $this
     */
    public function setTemplateIdentifier($templateIdentifier)
    {
        $this->templateIdentifier = $templateIdentifier;
        return $this;
    }

    /**
     * Set template vars
     *
     * @param array $templateVars
     * @return $this
     */
    public function setTemplateVars($templateVars)
    {
        $this->templateVars = $templateVars;
        return $this;
    }

    /**
     * Set template options
     *
     * @param array $templateOptions
     * @return $this
     */
    public function setTemplateOptions($templateOptions)
    {
        $this->templateOptions = $templateOptions;
        return $this;
    }

    /**
     * Get mail transport
     *
     * @return \Magento\Mail\TransportInterface
     */
    public function getTransport()
    {
        $this->prepareMessage();

        $result = $this->objectManager->create(
            'Magento\Mail\TransportInterface',
            array('message' => clone $this->message)
        );

        $this->reset();

        return $result;
    }

    /**
     * Reset object state
     *
     * @return $this
     */
    protected function reset()
    {
        $this->message = $this->objectManager->create('Magento\Mail\Message');
        $this->templateIdentifier = null;
        $this->templateVars = null;
        $this->templateOptions = null;
        return $this;
    }

    /**
     * Get template
     *
     * @return \Magento\Mail\TemplateInterface
     */
    protected function getTemplate()
    {
        return $this->templateFactory->get(
            $this->templateIdentifier
        )->setVars(
            $this->templateVars
        )->setOptions(
            $this->templateOptions
        );
    }

    /**
     * Prepare message
     *
     * @return $this
     */
    protected function prepareMessage()
    {
        $template = $this->getTemplate();
        $types = array(
            \Magento\App\TemplateTypesInterface::TYPE_TEXT => \Magento\Mail\MessageInterface::TYPE_TEXT,
            \Magento\App\TemplateTypesInterface::TYPE_HTML => \Magento\Mail\MessageInterface::TYPE_HTML
        );

        $body = $template->processTemplate();
        $this->message->setMessageType(
            $types[$template->getType()]
        )->setBody(
            $body
        )->setSubject(
            $template->getSubject()
        );

        return $this;
    }
}
