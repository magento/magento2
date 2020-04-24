<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Block\Header;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Html\Link;
use Magento\Framework\View\Element\Template\Context;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session;

/**
 * Remember Me block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Additional extends Link
{
    /**
     * @var Session
     */
    private $persistentSessionHelper;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var Data
     */
    private $persistentHelper;

    /**
     * @var string
     */
    protected $_template = 'Magento_Persistent::additional.phtml';

    /**
     * @param Context $context
     * @param Session $persistentSessionHelper
     * @param array $data
     * @param Json $jsonSerializer
     * @param Data $persistentHelper
     */
    public function __construct(
        Context $context,
        Session $persistentSessionHelper,
        Json $jsonSerializer,
        Data $persistentHelper,
        array $data = []
    ) {
        $this->isScopePrivate = true;
        $this->persistentSessionHelper = $persistentSessionHelper;
        $this->jsonSerializer = $jsonSerializer;
        $this->persistentHelper = $persistentHelper;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Retrieve unset cookie link
     *
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl('persistent/index/unsetCookie');
    }

    /**
     * Get customer id.
     *
     * @return int
     */
    public function getCustomerId(): int
    {
        return $this->persistentSessionHelper->getSession()->getCustomerId();
    }

    /**
     * Get persistent config.
     *
     * @return string
     */
    public function getConfig(): string
    {
        return $this->jsonSerializer->serialize(
            [
                'expirationLifetime' => $this->persistentHelper->getLifeTime(),
            ]
        );
    }
}
