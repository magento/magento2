<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Block;

use Magento\Framework\View\Element\Template;
use Magento\Payment\Model\CcConfigProvider;
use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Class AbstractCardRenderer
 * @api
 */
abstract class AbstractCardRenderer extends Template implements CardRendererInterface
{
    /**
     * @var PaymentTokenInterface|null
     */
    private $token;

    /**
     * @var array|null
     */
    private $tokenDetails;

    /**
     * @var CcConfigProvider
     */
    private $iconsProvider;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param CcConfigProvider $iconsProvider
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CcConfigProvider $iconsProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->iconsProvider = $iconsProvider;
    }

    /**
     * Renders specified token
     *
     * @param PaymentTokenInterface $token
     * @return string
     */
    public function render(PaymentTokenInterface $token)
    {
        $this->token = $token;
        $this->tokenDetails = json_decode($this->getToken()->getTokenDetails() ?: '{}', true);
        $result = $this->toHtml();
        $this->token = null;
        $this->tokenDetails = null;

        return $result;
    }

    /**
     * @return PaymentTokenInterface
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return array
     */
    protected function getTokenDetails()
    {
        return $this->tokenDetails;
    }

    /**
     * @param string $type
     * @return array
     */
    protected function getIconForType($type)
    {
        if (isset($this->iconsProvider->getIcons()[$type])) {
            return $this->iconsProvider->getIcons()[$type];
        }

        return [
            'url' => '',
            'width' => 0,
            'height' => 0
        ];
    }
}
