<?php


namespace SeasonFive\HtmlValidator;

/**
 * Represents single validation error.
 */
class ValidationError
{
    /**
     * @var array path to the tag where any of validation rules is violated.
     */
    private $path;

    /**
     * @var ?string name of the attribute violating some of validation rules.
     * When this error is not about an attribute, it's `null`.
     */
    private $attr;

    /**
     * @var ?string reason for validation error
     */
    private $reason;

    /**
     * Creates a validation error entry.
     *
     * @param string $reason reason for the error
     * @param array $path path to the tag violating the rules
     * @param ?string $attr name of the attribute violating the rules. `null` if this error is not about an attribute
     */
    public function __construct(string $reason, array $path, string $attr = null)
    {
        $this->path = $path;
        $this->attr = $attr;
        $this->reason = $reason;
    }

    /**
     * Returns path to the tag violating the validation rules
     *
     * @return array the tag path
     */
    public function getPath(): array
    {
        return $this->path;
    }

    /**
     * Returns the name of the attribute violating rules if applicable.
     *
     * @return ?string attribute name
     */
    public function getAttr(): string
    {
        return $this->attr;
    }

    /**
     * Returns the detailed reason for rejecting a tag or attribute.
     *
     * @return string the detailed reason for the error
     */
    public function getReason(): string
    {
        return $this->reason;
    }
}