<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Widget\Grid;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Json\EncoderInterface;

/**
 * Grid widget massaction default block
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Massaction extends \Magento\Backend\Block\Widget\Grid\Massaction\AbstractMassaction
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * Map bind item id to a particular acl type
     * itemId => acl
     *
     * @var array
     */
    private $restrictions = [
        'enable'  => 'Magento_Backend::toggling_cache_type',
        'disable' => 'Magento_Backend::toggling_cache_type',
        'refresh' => 'Magento_Backend::refresh_cache_type',
    ];

    /**
     * Massaction constructor.
     *
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param array $data
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        array $data = [],
        AuthorizationInterface $authorization = null
    ) {
        $this->authorization = $authorization ?: ObjectManager::getInstance()->get(AuthorizationInterface::class);

        parent::__construct($context, $jsonEncoder, $data);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $itemId
     * @param array|DataObject $item
     *
     * @return $this
     */
    public function addItem($itemId, $item)
    {
        if (!$this->isRestricted($itemId)) {
            parent::addItem($itemId, $item);
        }

        return $this;
    }

    /**
     * Check if access to action restricted
     *
     * @param string $itemId
     *
     * @return bool
     */
    private function isRestricted(string $itemId): bool
    {
        if (!key_exists($itemId, $this->restrictions)) {
            return false;
        }

        return !$this->authorization->isAllowed($this->restrictions[$itemId]);
    }
}
