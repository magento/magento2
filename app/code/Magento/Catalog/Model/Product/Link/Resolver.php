<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Product\Link;

/**
 * @api
 * @since 101.0.0
 */
class Resolver
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     * @since 101.0.0
     */
    protected $request;

    /**
     * @var null|array
     * @since 101.0.0
     */
    protected $links = null;

    /**
     * Resolver constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * Get stored value.
     * Fallback to request if none.
     *
     * @return array|null
     * @since 101.0.0
     */
    public function getLinks()
    {
        if (null === $this->links) {
            $this->links = (array)$this->request->getParam('links', []);
        }
        return $this->links;
    }

    /**
     * Override link data from request
     *
     * @param array|null $links
     * @return void
     * @since 101.0.0
     */
    public function override($links)
    {
        $this->links = $links;
    }
}
