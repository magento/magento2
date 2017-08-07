<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\Config;
use Magento\Vault\Model\Ui\Adminhtml\TokensConfigProvider;
use Magento\Payment\Model\CcConfigProvider;

/**
 * Class Form
 * @since 2.1.0
 */
class Form extends \Magento\Payment\Block\Form
{
    /**
     * @var TokensConfigProvider
     * @since 2.1.0
     */
    private $tokensProvider;

    /**
     * @var CcConfigProvider
     * @since 2.1.0
     */
    private $cardConfigProvider;

    /**
     * @param Context $context
     * @param TokensConfigProvider $tokensConfigProvider
     * @param CcConfigProvider $ccConfigProvider
     * @param array $data
     * @since 2.1.0
     */
    public function __construct(
        Context $context,
        TokensConfigProvider $tokensConfigProvider,
        CcConfigProvider $ccConfigProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->tokensProvider = $tokensConfigProvider;
        $this->cardConfigProvider = $ccConfigProvider;
    }

    /**
     * @inheritdoc
     * @since 2.1.0
     */
    protected function _prepareLayout()
    {
        $this->createVaultBlocks();
        return $this;
    }

    /**
     * Create block for own configuration for each payment token
     *
     * @return void
     * @since 2.1.0
     */
    protected function createVaultBlocks()
    {
        $icons = $this->cardConfigProvider->getIcons();
        $payments = $this->tokensProvider->getTokensComponents($this->_nameInLayout);
        foreach ($payments as $key => $payment) {
            $this->addChild(
                $key,
                $payment->getName(),
                array_merge(
                    ['id' => $this->_nameInLayout . $key, 'icons' => $icons],
                    $payment->getConfig()
                )
            );
        }
    }
}
