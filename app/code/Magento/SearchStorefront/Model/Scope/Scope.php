<?php

namespace Magento\SearchStorefront\Model\Scope;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreExtensionInterface;

/**
 * Scope model stub for search service
 */
class Scope extends \Magento\Framework\DataObject implements \Magento\Framework\App\ScopeInterface
{
    public function getId()
    {
        return (int)$this->getData('id');
    }

    public function getCode()
    {
        return (string)$this->getData('code');
    }

    public function getName()
    {
        return (string)$this->getData('name');
    }

    /**
     * @inheritDoc
     */
    public function getScopeType()
    {
        return (string)$this->getData('scope_type');
    }

    /**
     * @inheritDoc
     */
    public function getScopeTypeName()
    {
        return 'search-service';
    }
}
