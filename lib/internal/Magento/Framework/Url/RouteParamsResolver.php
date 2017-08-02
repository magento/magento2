<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

use Magento\Framework\Url\RouteParamsResolverInterface;

/**
 * Route params resolver.
 *
 * @method $this setType(string $type)
 * @method string getType()
 * @method $this setScope(string $scope)
 * @method string getScope()
 * @method $this setSecureIsForced(bool $isForced)
 * @method bool getSecureIsForced()
 * @method $this setSecure(bool $isForced)
 * @method bool getSecure()
 * @since 2.0.0
 */
class RouteParamsResolver extends \Magento\Framework\DataObject implements RouteParamsResolverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     * @since 2.0.0
     */
    protected $request;

    /**
     * @var \Magento\Framework\Url\QueryParamsResolverInterface
     * @since 2.0.0
     */
    protected $queryParamsResolver;

    /**
     * @var \Magento\Framework\Escaper
     * @since 2.2.0
     */
    protected $escaper;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Url\QueryParamsResolverInterface $queryParamsResolver
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Url\QueryParamsResolverInterface $queryParamsResolver,
        array $data = []
    ) {
        parent::__construct($data);
        $this->request = $request;
        $this->queryParamsResolver = $queryParamsResolver;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function setRouteParams(array $data, $unsetOldParams = true)
    {
        if (isset($data['_type'])) {
            $this->setType($data['_type']);
            unset($data['_type']);
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
                    if (array_key_exists($key, $data) || !$this->request->getUserParam($key)) {
                        continue;
                    }
                    $data[$key] = $this->request->getUserParam($key);
                }
            } elseif ($data['_current']) {
                foreach ($this->request->getUserParams() as $key => $value) {
                    if (array_key_exists($key, $data) || $this->getRouteParam($key)) {
                        continue;
                    }
                    $data[$key] = $value;
                }
                foreach ($this->request->getQuery() as $key => $value) {
                    $this->queryParamsResolver->setQueryParam($key, $value);
                }
            }
            unset($data['_current']);
        }

        if (isset($data['_use_rewrite'])) {
            unset($data['_use_rewrite']);
        }

        foreach ($data as $key => $value) {
            if (!is_scalar($value) || $key == 'key' || !$this->getData('escape_params')) {
                $this->setRouteParam($key, $value);
            } else {
                $this->setRouteParam(
                    $this->getEscaper()->encodeUrlParam($key),
                    $this->getEscaper()->encodeUrlParam($value)
                );
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getRouteParams()
    {
        return $this->_getData('route_params');
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getRouteParam($key)
    {
        return $this->getData('route_params', $key);
    }

    /**
     * Get escaper
     *
     * @return \Magento\Framework\Escaper
     * @deprecated 2.2.0
     * @since 2.2.0
     */
    private function getEscaper()
    {
        if ($this->escaper == null) {
            $this->escaper = \Magento\Framework\App\ObjectManager::getInstance()
                    ->get(\Magento\Framework\Escaper::class);
        }
        return $this->escaper;
    }
}
