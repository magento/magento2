<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorldFrontendUi\Block;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as ExtraSession;
use Mod\HelloWorldApi\Api\ExtraAbilitiesProviderInterface as Provider;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Mod\HelloWorld\Model\ApprovedExtraCommentsLoader;

/**
 * HelloWorldFrontendUi product extra comment form block.
 */
class Form extends Template
{
    /**
     * @var ExtraSession
     */
    private $session;

    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var ExtensibleDataObjectConverter
     */
    private $dataObjectConverter;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var ApprovedExtraCommentsLoader
     */
    private $commentLoader;

    /**
     * @param Context $context
     * @param ExtraSession $session
     * @param Provider $provider
     * @param ExtensibleDataObjectConverter $dataObjectConverter
     * @param Registry $registry
     * @param ApprovedExtraCommentsLoader $commentLoader
     * @param array $data
     */
    public function __construct(
        Context $context,
        ExtraSession $session,
        Provider $provider,
        ExtensibleDataObjectConverter $dataObjectConverter,
        Registry $registry,
        ApprovedExtraCommentsLoader $commentLoader,
        array $data = []
    ) {
        $this->session = $session;
        $this->provider = $provider;
        $this->dataObjectConverter = $dataObjectConverter;
        $this->coreRegistry = $registry;
        $this->commentLoader = $commentLoader;
        parent::__construct($context, $data);
    }

    /**
     * Get current customer id.
     *
     * @return null|int
     */
    public function getCustomerId()
    {
        $customerId = $this->session->getCustomerId();
        if ($customerId !== null) {
            return $customerId;
        }
        return null;
    }

    /**
     * Check for permission to add extra comment.
     *
     * @return bool
     */
    public function checkIsAllow(): bool
    {
        $customerExtraAttributesObject = $this->provider->getExtraAbilities((int)$this->getCustomerId());
        if (!empty($customerExtraAttributesObject)) {
            $customerExtraAttributes = $this->dataObjectConverter->toFlatArray($customerExtraAttributesObject[0], []);
            if ($customerExtraAttributes['is_allowed_add_description'] == 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * Create hidden input with index controller url.
     *
     * @return string
     */
    public function getIndexUrl(): string
    {
        $urlInput = '<input id="ajaxUrl" type="hidden" value="none">';
        if ($this->checkIsAllow() === true) {
            $url = $this->getUrl('helloworld/ajax/index');
            $urlInput = '<input id="ajaxUrl" type="hidden" value="' . $url . '">';

            return $urlInput;
        }
        return $urlInput;
    }

    /**
     * Create hidden input with index controller url.
     *
     * @return string
     */
    public function getInput(): string
    {
        if ($this->checkIsAllow() === true) {
            $textInput = '<textarea id="extraComment" aria-label="extraComment"' .
                ' aria-required="true" cols="5" rows="3" class="myTextarea"></textarea>';

            return $textInput;
        } else {
            return 'You have not permissions to add extra comment.';
        }
    }

    /**
     * Create hidden input with product SKU.
     *
     * @return string
     */
    public function getProductSku(): string
    {
        $skuInput = '<input id="ajaxUrl" type="hidden" value="none">';
        if ($this->checkIsAllow() === true) {
            $productObject = $this->coreRegistry->registry('current_product');
            $productSku = $productObject->getSku();
            $skuInput = '<input id="ajaxSku" type="hidden" value="' . $productSku . '">';

            return $skuInput;
        }
        return $skuInput;
    }
}
