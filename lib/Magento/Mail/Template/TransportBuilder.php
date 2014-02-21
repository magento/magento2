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
     * @param FactoryInterface $templateFactory
     * @param \Magento\Mail\Message $message
     * @param \Magento\ObjectManager $objectManager
     */
    public function __construct(
        \Magento\Mail\Template\FactoryInterface $templateFactory,
        \Magento\Mail\Message $message,
        \Magento\ObjectManager $objectManager
    ) {
        $this->templateFactory = $templateFactory;
        $this->message = $message;
        $this->objectManager = $objectManager;
    }

    /**
     * Add cc address
     *
     * @param array|string $address
     * @return $this
     */
    public function addCc($address)
    {
        $this->message->addCc($address);
        return $this;
    }

    /**
     * Set mail from address
     *
     * @param string $from
     * @return $this
     */
    public function setFrom($from)
    {
        $this->message->setFrom($from);
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
        $template = $this->templateFactory->get($this->templateIdentifier)
            ->setVars($this->templateVars)
            ->setOptions($this->templateOptions);
        $types = array(
            \Magento\Mail\Template\TemplateInterface::TYPE_TEXT => \Magento\Mail\MessageInterface::TYPE_TEXT,
            \Magento\Mail\Template\TemplateInterface::TYPE_HTML => \Magento\Mail\MessageInterface::TYPE_HTML,
        );

        $this->message->setMessageType($types[$template->getType()])
            ->setBody($template->processTemplate())
            ->setSubject($template->getSubject());

        return $this->objectManager->create('\Magento\Mail\TransportInterface', array('message' => $this->message));

    }
}