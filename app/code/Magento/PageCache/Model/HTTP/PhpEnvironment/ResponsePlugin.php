<?php

namespace Magento\PageCache\Model\HTTP\PhpEnvironment;

use Magento\Framework\HTTP\PhpEnvironment\Response as Subject;
use Magento\PageCache\Model\Config;
use Zend\Http\HeaderLoader;
use Magento\PageCache\Model\HTTP\Header\XMagentoTags;

class ResponsePlugin
{
    /**
     * Approximately 8kb in length
     *
     * @var int
     */
    private $requestSize = 8000;

    /**
     * PageCache configuration
     *
     * @var Config
     */
    private $config;

    /**
     * ResponsePlugin constructor.
     *
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Special case for handling X-Magento-Tags header
     * splits very long header into multiple headers
     *
     * @param Subject  $subject
     * @param \Closure $proceed
     * @param string   $name
     * @param string   $value
     * @param bool     $replace
     *
     * @return Subject|mixed
     */
    public function aroundSetHeader(Subject $subject, \Closure $proceed, $name, $value, $replace = false)
    {
        //if varnish isn't enabled, don't do anything
        if (!$this->config->isEnabled() || $this->config->getType() != Config::VARNISH) {
            return $proceed($name, $value, $replace);
        }

        $this->addHeaderToStaticMap();

        if ($name == 'X-Magento-Tags') {
            $headerLength = 0;
            $value = (string)$value;
            $tags = explode(',', $value);

            $newTags = [];
            foreach ($tags as $tag) {
                if ($headerLength + strlen($tag) > $this->requestSize - count($tags) - 1) {
                    $tagString = implode(',', $tags);
                    $subject->getHeaders()->addHeaderLine($name, $tagString);
                    $newTags = [];
                    $headerLength = 0;
                }
                $headerLength += strlen($tag);
                $newTags[] = $tag;
            }

            return $subject;
        }

        return $proceed($name, $value, $replace);
    }

    /**
     * Add X-Magento-Tags header to HeaderLoader static map
     */
    private function addHeaderToStaticMap()
    {
        HeaderLoader::addStaticMap(
            [
                'xmagentotags' => XMagentoTags::class,
            ]
        );
    }

}