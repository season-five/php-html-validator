<?php


namespace SeasonFive\HtmlValidator;


class ParseError
{
    /**
     * @var string
     */
    private $reason;
    /**
     * @var int
     */
    private $line;
    /**
     * @var int
     */
    private $column;

    /**
     * ParseError constructor.
     * @param string $reason
     * @param int $line
     * @param int $column
     */
    public function __construct(string $reason, int $line, int $column)
    {
        $this->reason = $reason;
        $this->line = $line;
        $this->column = $column;
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * @return int
     */
    public function getColumn(): int
    {
        return $this->column;
    }

    /**
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }
}