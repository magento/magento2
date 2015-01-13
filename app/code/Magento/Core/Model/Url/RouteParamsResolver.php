<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\Url;

class RouteParamsResolver extends \Magento\Framework\Object implements \Magento\Framework\Url\RouteParamsResolverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Url\QueryParamsResolverInterface
     */
    protected $_queryParamsResolver;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Url\QueryParamsResolverInterface $queryParamsResolver
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Url\QueryParamsResolverInterface $queryParamsResolver,
        array $data = []
    ) {
        parent::__construct($data);
        $this->_request = $request;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_queryParamsResolver = $queryParamsResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function setRouteParams(array $data, $unsetOldParams = true)
    {
        if (isset($data['_type'])) {
            $this->setType($data['_type']);
            unset($data['_type']);
        }

        if (isset($data['_scope'])) {
            $this->setScope($data['_scope']);
            unset($data['_scope']);
        }

        if (isset($data['_forced_secure'])) {
            $this->setSecure((bool)$data['_forced_secure']);
            $this->setSecureIsForced(true);
            unset($data['_forced_secure']);
        } elseif (isset($data['_secure'])) {
            $this->setSecure((bool)$data['_secure']);
            unset($data['_secure']);
        }

        if (isset($data['_absolute'])) {
            unset($data['_absolute']);
        }

        if ($unsetOldParams) {
            $this->unsetData('route_params');
        }

        if (isset($data['_current'])) {
            if (is_array($data['_current'])) {
                foreach ($data['_current'] as $key) {
                    if (array_key_exists($key, $data) || !$this->_request->getUserParam($key)) {
                        continue;
                    }
                    $data[$key] = $this->_request->getUserParam($key);
                }
            } elseif ($data['_current']) {
                foreach ($this->_request->getUserParams() as $key => $value) {
                    if (array_key_exists($key, $data) || $this->getRouteParam($key)) {
                        continue;
                    }
                    $data[$key] = $value;
                }
                foreach ($this->_request->getQuery() as $key => $value) {
                    $this->_queryParamsResolver->setQueryParam($key, $value);
                }
            }
            unset($data['_current']);
        }

        if (isset($data['_use_rewrite'])) {
            unset($data['_use_rewrite']);
        }

        if (isset($data['_scope_to_url']) && (bool)$data['_scope_to_url'] === true) {
            $store = $this->getScope() ?: $this->_storeManager->getStore();
            if (!$this->_scopeConfig->getValue(
                \Magento\Store\Model\Store::XML_PATH_STORE_IN_URL,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->getScope()
            ) && !$this->_storeManager->hasSingleStore()
            ) {
                $this->_queryParamsResolver->setQueryParam('___store', $store->getCode());
            }
        }
        unset($data['_scope_to_url']);

        foreach ($data as $key => $value) {
            $this->setRouteParam($key, $value);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRouteParam($key, $data)
    {
        $params = $this->_getData('route_params');
        if (isset($params[$key]) && $params[$key] == $data) {
            return $this;
        }
        $params[$key] = $data;
        $this->unsetData('route_path');
        return $this->setData('route_params', $params);
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteParams()
    {
        return $this->_getData('route_params');
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteParam($key)
    {
        return $this->getData('route_params', $key);
    }
}
