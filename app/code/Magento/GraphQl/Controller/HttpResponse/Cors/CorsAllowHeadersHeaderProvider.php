<?php


namespace Magento\GraphQl\Controller\HttpResponse\Cors;

use Magento\Framework\App\Response\HeaderProvider\HeaderProviderInterface;
use Magento\GraphQl\Model\Cors\ConfigurationInterface;

class CorsAllowHeadersHeaderProvider implements HeaderProviderInterface
{
    protected $headerName = 'Access-Control-Allow-Headers';

    protected $headerValue = '';

    /**
     * CORS configuration provider
     *
     * @var \Magento\GraphQl\Model\Cors\ConfigurationInterface
     */
    private $corsConfiguration;

    public function __construct(ConfigurationInterface $corsConfiguration)
    {
        $this->corsConfiguration = $corsConfiguration;
    }

    public function getName()
    {
        return $this->headerName;
    }

    public function canApply() : bool
    {
        return $this->corsConfiguration->isEnabled() && $this->getValue();
    }

    public function getValue()
    {
        return $this->corsConfiguration->getAllowedHeaders()
            ? $this->corsConfiguration->getAllowedHeaders()
            : $this->headerValue;
    }
}
