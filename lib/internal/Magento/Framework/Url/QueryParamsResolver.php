<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

/**
 * Class \Magento\Framework\Url\QueryParamsResolver
 *
 * @since 2.0.0
 */
class QueryParamsResolver extends \Magento\Framework\DataObject implements QueryParamsResolverInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getQuery($escape = false)
    {
        if (!$this->hasData('query')) {
            $query = '';
            $params = $this->getQueryParams();
            if (is_array($params)) {
                ksort($params);
                $query = http_build_query($params, '', $escape ? '&amp;' : '&');
            }
            $this->setData('query', $query);
        }
        return $this->_getData('query');
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setQuery($data)
    {
        if ($this->_getData('query') !== $data) {
            $this->unsetData('query_params');
            $this->setData('query', $data);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setQueryParam($key, $data)
    {
        $params = $this->getQueryParams();
        if (isset($params[$key]) && $params[$key] == $data) {
            return $this;
        }
        $params[$key] = $data;
        $this->unsetData('query');
        $this->setData('query_params', $params);
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getQueryParams()
    {
        if (!$this->hasData('query_params')) {
            $params = [];
            if ($this->_getData('query')) {
                foreach (explode('&', $this->_getData('query')) as $param) {
                    $paramArr = explode('=', $param);
                    $params[$paramArr[0]] = urldecode($paramArr[1]);
                }
            }
            $this->setData('query_params', $params);
        }
        return $this->_getData('query_params');
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setQueryParams(array $data)
    {
        return $this->setData('query_params', $data);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function addQueryParams(array $data)
    {
        $this->unsetData('query');

        if ($this->_getData('query_params') == $data) {
            return $this;
        }

        $params = $this->_getData('query_params');
        if (!is_array($params)) {
            $params = [];
        }
        foreach ($data as $param => $value) {
            $params[$param] = $value;
        }
        $this->setData('query_params', $params);

        return $this;
    }
}
