<?php


namespace SeasonFive\HtmlValidator;

/**
 * Checks if a given item is a match or not.
 */
interface Matcher
{
    /**
     * Checks if the given `$path` matches the rule set by this `Matcher`.
     *
     * @param array $path the path to the item to match, which is either a tag or attribute
     * @param object $context additional data associated with the item to match
     * @param string &$reason assigned the reason on return for the result whether it is matched or not
     * @return bool `true` if matched. `false` otherwise
     */
    public function match(array $path, $context, string &$reason): bool;
}