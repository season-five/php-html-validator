<?php


namespace SeasonFive\HtmlValidator;

/**
 * Additional data associated with a tag.
 */
class TagContext
{
    /**
     * @var array attributes of the associated tag.
     */
    private $attrs;

    /**
     * @var bool whether this tag is self closing or not.
     */
    private $selfClosing;

    /**
     * Creates a context object from the given data.
     *
     * @param array $attrs tag attributes
     * @param bool $selfClosing whether the tag is self closing
     */
    public function __construct(array $attrs, bool $selfClosing)
    {
        $this->attrs = $attrs;
        $this->selfClosing = $selfClosing;
    }

    /**
     * @return array
     */
    public function getAttrs(): array
    {
        return $this->attrs;
    }

    /**
     * @return bool
     */
    public function isSelfClosing(): bool
    {
        return $this->selfClosing;
    }
}