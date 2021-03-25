<?php

namespace SeasonFive\HtmlValidator;

use InvalidArgumentException;
use Masterminds\HTML5\{Exception, Parser\Scanner, Parser\Tokenizer};
use SeasonFive\HtmlValidator\Impl\{InvalidHtmlException, Rules, ValidatingEventHandler};

/**
 * HTML validator.
 */
class Validator
{
    const TAGS = 'tags';
    const ATTRS = 'attrs';

    private $tagRules;
    private $attrRules;

    /**
     * Creates a `Validator` with the give `$config`.
     *
     * @param array $config what elements/attributes to reject or allow
     */
    public function __construct(array $config)
    {
        if (key_exists(self::TAGS, $config)) {
            try {
                $this->tagRules = new Rules($config[self::TAGS]);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException("errors in tag rules", 0, $e);
            }
        }

        if (key_exists(self::ATTRS, $config)) {
            try {
                $this->attrRules = new Rules($config[self::ATTRS]);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException("errors in attribute rules", 0, $e);
            }
        }
    }

    /**
     * Validates the given `$html`.
     *
     * @param string $html the HTML string to validate. Must be encoded in UTF-8
     * @param bool $failFast if `true`, validation will fail on the first error
     * @param array &$errors assigned an array of validation errors found in the course of the validation
     * @return bool if valid against the rule set on creation
     */
    public function validate(string $html, bool $failFast = true, array &$errors = null): bool
    {
        try {
            $scanner = new Scanner($html);
            $eventHandler = new ValidatingEventHandler($this->tagRules, $this->attrRules, $failFast);
            $tokenizer = new Tokenizer($scanner, $eventHandler);
            $tokenizer->parse();

            $errors = $eventHandler->getErrors();

            return empty($errors);
        } catch (InvalidHtmlException $e) {
            return false;
        } catch (Exception $e) {
            throw new InvalidArgumentException("$html is not a valid UTF-8 string: {$e->getMessage()}");
        }
    }
}