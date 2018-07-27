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
 */
class Form extends \Magento\Payment\Block\Form
{
    /**
     * @var TokensConfigProvider
     */
    private $tokensProvider;

    /**
     * @var CcConfigProvider
     */
    private $cardConfigProvider;

    /**
     * @param Context $context
     * @param TokensConfigProvider $tokensConfigProvider
     * @param CcConfigProvider $ccConfigProvider
     * @param array $data
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
