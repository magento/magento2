<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\PageCache;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Page unique identifier
 */
class Identifier
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $context;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var Identifier\ModifierInterface[]
     */
    private $modifiers;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Http\Context $context
     * @param Json|null $serializer
     * @param array $modifiers
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Http\Context $context,
        Json $serializer = null,
        array $modifiers = []
    ) {
        $this->request = $request;
        $this->context = $context;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->modifiers = $modifiers;
    }

    /**
     * Return list of parameters to use while forming page cache
     *
     * @return string[]
     */
    public function getParameters() {
        $data = [
            $this->request->isSecure(),
            $this->request->getUriString(),
            $this->request->get(\Magento\Framework\App\Response\Http::COOKIE_VARY_STRING)
                ?: $this->context->getVaryString()
        ];

        /**
         * Add parameters appended through di
         */
        foreach ($this->modifiers as $modifier) {
            if ($modifier instanceof Identifier\ModifierInterface) {
                $data[] = $modifier->getData();
            }
        }

        return $data;
    }

    /**
     * Return unique page identifier
     *
     * @return string
     */
    public function getValue()
    {
        return sha1($this->serializer->serialize($this->getParameters()));
    }
}
