<?php
/**
 * JsonSchema
 * @filesource
 */
namespace JsonSchema\Uri\Retrievers;

/**
 * AbstractRetriever implements the default shared behavior
 * that all decendant Retrievers should inherit
 * @author Steven Garcia <webwhammy@gmail.com>
 */
abstract class AbstractRetriever implements UriRetrieverInterface
{
    /**
     * Media content type
     * @var string
     */
    protected $contentType;

    /**
     * {@inheritDoc}
     * @see \JsonSchema\Uri\Retrievers\UriRetrieverInterface::getContentType()
     */
    public function getContentType()
    {
        return $this->contentType;
    }
}
