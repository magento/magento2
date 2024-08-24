<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Model;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Area;
use Magento\Framework\Validator\GlobalForbiddenPatterns;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

class Mail implements MailInterface
{
    /**
     * @var ConfigInterface
     */
    private $contactsConfig;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var GlobalForbiddenPatterns
     */
    private $forbiddenPatternsValidator;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Initialize dependencies.
     *
     * @param ConfigInterface $contactsConfig
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param StoreManagerInterface|null $storeManager
     * @param GlobalForbiddenPatterns $forbiddenPatternsValidator
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ConfigInterface $contactsConfig,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager = null,
        GlobalForbiddenPatterns $forbiddenPatternsValidator,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->contactsConfig = $contactsConfig;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $this->forbiddenPatternsValidator = $forbiddenPatternsValidator;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Send email from contact form
     *
     * @param string $replyTo
     * @param array $variables
     * @return void
     * @throws LocalizedException
     */
    public function send($replyTo, array $variables)
    {
        // Check if regex validation is enabled
        $isRegexEnabled = $this->scopeConfig->isSetFlag(
            GlobalForbiddenPatterns::XML_PATH_SECURITY_REGEX_ENABLED,
            ScopeInterface::SCOPE_STORE
        );

        // Perform regex validation only if it's enabled
        if ($isRegexEnabled) {
            foreach ($variables['data'] as $key => $value) {
                if (is_string($value) && !$this->forbiddenPatternsValidator->isValid($value)) {
                    throw new LocalizedException(
                        __("Field %1 contains invalid characters.", $key)
                    );
                }
            }
        }

        /** @see \Magento\Contact\Controller\Index\Post::validatedParams() */
        $replyToName = !empty($variables['data']['name']) ? $variables['data']['name'] : null;

        $this->inlineTranslation->suspend();
        try {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($this->contactsConfig->emailTemplate())
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getId()
                    ]
                )
                ->setTemplateVars($variables)
                ->setFrom($this->contactsConfig->emailSender())
                ->addTo($this->contactsConfig->emailRecipient())
                ->setReplyTo($replyTo, $replyToName)
                ->getTransport();

            $transport->sendMessage();
        } finally {
            $this->inlineTranslation->resume();
        }
    }
}
