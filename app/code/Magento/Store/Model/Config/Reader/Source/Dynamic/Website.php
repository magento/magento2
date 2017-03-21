<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Reader\Source\Dynamic;

use Magento\Framework\App\Config\Scope\Converter;
use Magento\Store\Model\ResourceModel\Config\Collection\ScopedFactory;
use Magento\Framework\App\Config\Reader\Source\SourceInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\WebsiteFactory;

/**
 * Class for retrieving configuration from DB by website scope
 */
class Website implements SourceInterface
{
    /**
     * @var ScopedFactory
     */
    private $collectionFactory;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var WebsiteFactory
     */
    private $websiteFactory;

    /**
     * @var DefaultScope
     */
    private $defaultScope;

    /**
     * @param ScopedFactory $collectionFactory
     * @param Converter $converter
     * @param WebsiteFactory $websiteFactory
     * @param DefaultScope $defaultScope
     */
    public function __construct(
        ScopedFactory $collectionFactory,
        Converter $converter,
        WebsiteFactory $websiteFactory,
        DefaultScope $defaultScope
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->converter = $converter;
        $this->websiteFactory = $websiteFactory;
        $this->defaultScope = $defaultScope;
    }

    /**
     * Retrieve config by website scope
     *
     * @param string|null $scopeCode
     * @return array
     */
    public function get($scopeCode = null)
    {
        try {
            $website = $this->websiteFactory->create();
            $website->load($scopeCode);
            $collection = $this->collectionFactory->create(
                ['scope' => ScopeInterface::SCOPE_WEBSITES, 'scopeId' => $website->getId()]
            );
            $config = [];
            foreach ($collection as $item) {
                $config[$item->getPath()] = $item->getValue();
            }
            return array_replace_recursive($this->defaultScope->get(), $this->converter->convert($config));
        } catch (\DomainException $e) {
            return [];
        }
    }
}
