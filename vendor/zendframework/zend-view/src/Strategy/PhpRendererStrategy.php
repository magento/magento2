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
use Zend\View\Renderer\PhpRenderer;
use Zend\View\ViewEvent;

class PhpRendererStrategy extends AbstractListenerAggregate
{
    /**
     * Placeholders that may hold content
     *
     * @var array
     */
    protected $contentPlaceholders = array('article', 'content');

    /**
     * @var PhpRenderer
     */
    protected $renderer;

    /**
     * Constructor
     *
     * @param  PhpRenderer $renderer
     */
    public function __construct(PhpRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Retrieve the composed renderer
     *
     * @return PhpRenderer
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Set list of possible content placeholders
     *
     * @param  array $contentPlaceholders
     * @return PhpRendererStrategy
     */
    public function setContentPlaceholders(array $contentPlaceholders)
    {
        $this->contentPlaceholders = $contentPlaceholders;
        return $this;
    }

    /**
     * Get list of possible content placeholders
     *
     * @return array
     */
    public function getContentPlaceholders()
    {
        return $this->contentPlaceholders;
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
     * Select the PhpRenderer; typically, this will be registered last or at
     * low priority.
     *
     * @param  ViewEvent $e
     * @return PhpRenderer
     */
    public function selectRenderer(ViewEvent $e)
    {
        return $this->renderer;
    }

    /**
     * Populate the response object from the View
     *
     * Populates the content of the response object from the view rendering
     * results.
     *
     * @param ViewEvent $e
     * @return void
     */
    public function injectResponse(ViewEvent $e)
    {
        $renderer = $e->getRenderer();
        if ($renderer !== $this->renderer) {
            return;
        }

        $result   = $e->getResult();
        $response = $e->getResponse();

        // Set content
        // If content is empty, check common placeholders to determine if they are
        // populated, and set the content from them.
        if (empty($result)) {
            $placeholders = $renderer->plugin('placeholder');
            foreach ($this->contentPlaceholders as $placeholder) {
                if ($placeholders->containerExists($placeholder)) {
                    $result = (string) $placeholders->getContainer($placeholder);
                    break;
                }
            }
        }
        $response->setContent($result);
    }
}
