<?php


namespace SeasonFive\HtmlValidator\Impl;

use Masterminds\HTML5\{Elements, Parser\EventHandler};
use SeasonFive\HtmlValidator\{ParseError, TagContext, ValidationError};


/**
 * An {@link EventHandler} performing HTML validation by rules.
 */
class ValidatingEventHandler implements EventHandler
{
    /**
     * @var array a list of errors filed during the validation. If a validation session fails fast,
     * it will contain at most single entry
     */
    private $errors = [];

    private $tagRules;

    private $attrRules;

    /**
     * @var bool if `true`, stops validation on first error.
     */
    private $failFast;

    /**
     * @var array all the tags from the root to the current one.
     */
    private $path = [];

    /**
     * Creates a event handler to validate a given HTML.
     *
     * @param ?Rules $tagRules tag rules. If `null`, allows all tags
     * @param ?Rules $attrRules attribute rules. If 'null', allows all attributes
     * @param bool $failFast if `true`, stops validation on first failure
     */
    public function __construct(Rules $tagRules = null, Rules $attrRules = null, bool $failFast = true)
    {
        $this->tagRules = $tagRules ?? new Rules(Rules::ALLOW_ALL);
        $this->attrRules = $attrRules ?? new Rules(Rules::ALLOW_ALL);
        $this->failFast = $failFast;
    }

    /**
     * @inheritDoc
     */
    public function doctype($name, $idType = 0, $id = null, $quirks = false)
    {
        // Does nothing
    }

    /**
     * @inheritDoc
     */
    public function startTag($name, $attributes = array(), $selfClosing = false): int
    {
        $this->path[] = $name;

        $context = new TagContext($attributes, $selfClosing);
        $reason = '';

        if (!$this->tagRules->shouldAllow($this->path, $context, $reason)) {
            $this->errors[] = new ValidationError($reason, $this->path);
            if ($this->failFast) {
                throw new InvalidHtmlException();
            }
        }

        foreach ($attributes as $attr => $value) {
            $reason = '';
            $pathToAttr = $this->path;
            $pathToAttr[] = $attr;
            if (!$this->attrRules->shouldAllow($pathToAttr, $value, $reason)) {
                $this->errors[] = new ValidationError($reason, $this->path, $attr);
                if ($this->failFast) {
                    throw new InvalidHtmlException();
                }
            }
        }

        array_pop($this->path);

        return Elements::element($name);
    }

    /**
     * @inheritDoc
     */
    public function endTag($name)
    {
        // Does nothing. Checks are done in startTag.
    }

    /**
     * @inheritDoc
     */
    public function comment($cdata)
    {
        // Does nothing
    }

    /**
     * @inheritDoc
     */
    public function text($cdata)
    {
        // Does nothing for the time being.
        // Still the element content is not used for validation.
    }

    /**
     * @inheritDoc
     */
    public function eof()
    {
        // Does nothing
    }

    /**
     * @inheritDoc
     */
    public function parseError($msg, $line, $col)
    {
        $this->errors[] = new ParseError($msg, $line, $col);
        if ($this->failFast) {
            throw new InvalidHtmlException();
        }
    }

    /**
     * @inheritDoc
     */
    public function cdata($data)
    {
        // Does nothing for the time being.
        // Still the element content is not used for validation.
    }

    /**
     * @inheritDoc
     */
    public function processingInstruction($name, $data = null)
    {
        // Does nothing
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}