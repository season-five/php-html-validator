<?php

namespace SeasonFive\HtmlValidator\Test;

use PHPUnit\Framework\TestCase;
use SeasonFive\HtmlValidator\{Matcher, ValidationError, Validator};

class ValidatorTest extends TestCase
{
    public function testDenyScriptTag()
    {
        $validator = new Validator(
          [
            'tags' => [
              'deny' => ['script']
            ]
          ]
        );

        $this->assertTrue($validator->validate("<html><a href='http://example.com'>link</a></html>"));
        $this->assertFalse($validator->validate("<html><a href='http://example.com'>link</a><script></script></html>"));
    }

    public function testAllowAOnly()
    {
        $validator = new Validator(
          [
            'tags' => [
              'allow' => ['a']
            ]
          ]
        );

        $this->assertTrue($validator->validate("<a href='http://example.com'>link</a>"));
        $this->assertFalse($validator->validate("<html><a href='http://example.com'>link</a><script></script></html>"));
    }

    public function testAllowAttrWithValue()
    {
        $validator = new Validator(
          [
            'tags' => [
              'allow' => ['a']
            ],
            'attrs' => [
              'allow' => [
                'href' => new AttrMatcher('http://example.com')
              ]
            ]
          ]
        );

        $this->assertTrue($validator->validate("<a href='http://example.com'>link</a>"));
        $this->assertFalse($validator->validate("<a href='http://example.com/test'>"));
    }

    public function testValidationError()
    {
        $validator = new Validator(
          [
            'tags' => [
              'allow' => ['a']
            ],
            'attrs' => [
              'deny' => [
                'href' => new AttrMatcher('http://example.com')
              ]
            ]
          ]
        );

        $this->assertFalse(
          $validator->validate("<a href='http://example.com'>link</a><table></table>", false, $errors)
        );
        $this->assertEquals([new ValidationError('match', ['a'], 'href'), new ValidationError('', ['table'])], $errors);
    }

    public function testParseError()
    {
        $validator = new Validator(
          [
            'tags' => [
              'allow' => ['a']
            ],
            'attrs' => [
              'deny' => [
                'href' => new AttrMatcher('http://example.com')
              ]
            ]
          ]
        );

        $this->assertFalse(
          $validator->validate("<a<>", false, $errors)
        );
        $this->assertEquals(1, $errors[0]->getLine());
        $this->assertEquals(2, $errors[0]->getColumn());
    }
}

class AttrMatcher implements Matcher
{
    private $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function match(array $path, $context, string &$reason): bool
    {
        if ($this->value === $context) {
            $reason = 'match';
            return true;
        }
        return false;
    }
}