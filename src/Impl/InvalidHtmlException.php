<?php


namespace SeasonFive\HtmlValidator\Impl;

use RuntimeException;

/**
 * An internal exception to stop parsing on the first error.
 */
class InvalidHtmlException extends RuntimeException
{
}