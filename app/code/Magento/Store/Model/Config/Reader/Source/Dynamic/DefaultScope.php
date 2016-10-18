<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Config\Reader\Source\Dynamic;

use Magento\Framework\App\Config\Scope\Converter;
use Magento\Store\Model\ResourceModel\Config\Collection\ScopedFactory;
use Magento\Framework\App\Config\Reader\Source\SourceInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class for retrieving configuration from DB by default scope
 */
class DefaultScope implements SourceInterface
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
     * @param ScopedFactory $collectionFactory
     * @param Converter $converter
     */
    public function __construct(
        ScopedFactory $collectionFactory,
        Converter $converter
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->converter = $converter;
    }

    /**
     * Retrieve config by default scope
     *
     * @param string|null $scopeCode
     * @return array
     */
    public function get($scopeCode = null)
    {
        try {
            $collection = $this->collectionFactory->create(
                ['scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT]
            );
        } catch (\DomainException $e) {
            $collection = [];
        }
        $config = [];
        foreach ($collection as $item) {
            $config[$item->getPath()] = $item->getValue();
        }
        return $this->converter->convert($config);
    }
}
