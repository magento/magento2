<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Block\Header;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Persistent\Helper\Data;

/**
 * Remember Me block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Additional extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerViewHelper;

    /**
     * @var \Magento\Persistent\Helper\Session
     */
    protected $_persistentSessionHelper;

    /**
     * Customer repository
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var string
     */
    protected $_template = 'Magento_Persistent::additional.phtml';

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var Data
     */
    private $persistentHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\View $customerViewHelper
     * @param \Magento\Persistent\Helper\Session $persistentSessionHelper
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param array $data
     * @param Json|null $jsonSerializer
     * @param Data|null $persistentHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\View $customerViewHelper,
        \Magento\Persistent\Helper\Session $persistentSessionHelper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        array $data = [],
        ?Json $jsonSerializer = null,
        ?Data $persistentHelper = null
    ) {
        $this->_isScopePrivate = true;
        $this->_customerViewHelper = $customerViewHelper;
        $this->_persistentSessionHelper = $persistentSessionHelper;
        $this->customerRepository = $customerRepository;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->jsonSerializer = $jsonSerializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->persistentHelper = $persistentHelper ?: ObjectManager::getInstance()->get(Data::class);
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
        return $this->_persistentSessionHelper->getSession()->getCustomerId();
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
