<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Strategy;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\View\Model;
use Zend\View\Renderer\JsonRenderer;
use Zend\View\ViewEvent;

class JsonStrategy extends AbstractListenerAggregate
{
    /**
     * Character set for associated content-type
     *
     * @var string
     */
    protected $charset = 'utf-8';

    /**
     * Multibyte character sets that will trigger a binary content-transfer-encoding
     *
     * @var array
     */
    protected $multibyteCharsets = array(
        'UTF-16',
        'UTF-32',
    );

    /**
     * @var JsonRenderer
     */
    protected $renderer;

    /**
     * Constructor
     *
     * @param  JsonRenderer $renderer
     */
    public function __construct(JsonRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RENDERER, array($this, 'selectRenderer'), $priority);
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RESPONSE, array($this, 'injectResponse'), $priority);
    }

    /**
     * Set the content-type character set
     *
     * @param  string $charset
     * @return JsonStrategy
     */
    public function setCharset($charset)
    {
        $this->charset = (string) $charset;
        return $this;
    }

    /**
     * Retrieve the current character set
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Detect if we should use the JsonRenderer based on model type and/or
     * Accept header
     *
     * @param  ViewEvent $e
     * @return null|JsonRenderer
     */
    public function selectRenderer(ViewEvent $e)
    {
        $model = $e->getModel();

        if (!$model instanceof Model\JsonModel) {
            // no JsonModel; do nothing
            return;
        }

        // JsonModel found
        return $this->renderer;
    }

    /**
     * Inject the response with the JSON payload and appropriate Content-Type header
     *
     * @param  ViewEvent $e
     * @return void
     */
    public function injectResponse(ViewEvent $e)
    {
        $renderer = $e->getRenderer();
        if ($renderer !== $this->renderer) {
            // Discovered renderer is not ours; do nothing
            return;
        }

        $result   = $e->getResult();
        if (!is_string($result)) {
            // We don't have a string, and thus, no JSON
            return;
        }

        // Populate response
        $response = $e->getResponse();
        $response->setContent($result);
        $headers = $response->getHeaders();

        if ($this->renderer->hasJsonpCallback()) {
            $contentType = 'application/javascript';
        } else {
            $contentType = 'application/json';
        }

        $contentType .= '; charset=' . $this->charset;
        $headers->addHeaderLine('content-type', $contentType);

        if (in_array(strtoupper($this->charset), $this->multibyteCharsets)) {
            $headers->addHeaderLine('content-transfer-encoding', 'BINARY');
        }
    }
}
