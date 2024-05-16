<?php
declare(strict_types=1);

namespace Magento\PageCache\Model\App\Request\Http;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\PageCache\IdentifierInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Identifier implements IdentifierInterface
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
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Http\Context $context
     * @param Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Http\Context $context,
        Json $serializer = null,
        private IdentifierStoreReader $identifierStoreReader
    ) {
        $this->request = $request;
        $this->context = $context;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Return unique page identifier
     *
     * @return string
     */
    public function getValue()
    {
        $data = [
            $this->request->isSecure(),
            $this->request->getUriString(),
            $this->request->get(\Magento\Framework\App\Response\Http::COOKIE_VARY_STRING)
                ?: $this->context->getVaryString()
        ];

        $data = $this->identifierStoreReader->getPageTagsWithStoreCacheTags($data);

        return sha1($this->serializer->serialize($data));
    }
}
