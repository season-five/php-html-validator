<?php

namespace SeasonFive\HtmlValidator\Test\Impl;

use PHPUnit\Framework\TestCase;
use SeasonFive\HtmlValidator\Impl\Rules;
use SeasonFive\HtmlValidator\Matcher;


class RulesTest extends TestCase
{
    public function testAllow()
    {
        $rules = new Rules(
          [
            'allow' => ['a', 'b']
          ]
        );
        $reason = '';

        $this->assertTrue($rules->shouldAllow(['a'], null, $reason));
        $this->assertTrue($rules->shouldAllow(['b'], null, $reason));

        $this->assertTrue($rules->shouldAllow(['k', 'a'], null, $reason));
        $this->assertTrue($rules->shouldAllow(['a', 'b'], null, $reason));

        $this->assertFalse($rules->shouldAllow(['c'], null, $reason));
        $this->assertFalse($rules->shouldAllow(['a', 'b', 'c'], null, $reason));
    }

    public function testOverridesOfAllow()
    {
        $rules = new Rules(
          [
            'allow' => ['a', 'b'],
            'overrides' => ['c' => ['a']]
          ]
        );
        $reason = '';

        $this->assertTrue($rules->shouldAllow(['a'], null, $reason));
        $this->assertTrue($rules->shouldAllow(['b'], null, $reason));

        $this->assertTrue($rules->shouldAllow(['k', 'a'], null, $reason));
        $this->assertTrue($rules->shouldAllow(['a', 'b'], null, $reason));

        $this->assertFalse($rules->shouldAllow(['c', 'a'], null, $reason));
    }


    public function testDeny()
    {
        $rules = new Rules(
          [
            'deny' => ['a', 'b']
          ]
        );
        $reason = '';

        $this->assertTrue($rules->shouldAllow(['c'], null, $reason));
        $this->assertTrue($rules->shouldAllow(['a', 'c'], null, $reason));

        $this->assertFalse($rules->shouldAllow(['a'], null, $reason));
        $this->assertFalse($rules->shouldAllow(['b'], null, $reason));
        $this->assertFalse($rules->shouldAllow(['c', 'b'], null, $reason));
        $this->assertFalse($rules->shouldAllow(['b', 'a'], null, $reason));
    }

    public function testOverridesOfDeny()
    {
        $rules = new Rules(
          [
            'deny' => ['a', 'b'],
            'overrides' => ['c' => ['a']]
          ]
        );
        $reason = '';

        $this->assertTrue($rules->shouldAllow(['c'], null, $reason));
        $this->assertTrue($rules->shouldAllow(['a', 'c'], null, $reason));

        $this->assertFalse($rules->shouldAllow(['a'], null, $reason));
        $this->assertFalse($rules->shouldAllow(['b'], null, $reason));
        $this->assertFalse($rules->shouldAllow(['c', 'b'], null, $reason));
        $this->assertFalse($rules->shouldAllow(['b', 'a'], null, $reason));

        $this->assertTrue($rules->shouldAllow(['c', 'a'], null, $reason));
    }

    public function testNestedAllow()
    {
        $rules = new Rules(
          [
            'allow' => ['a' => ['b' => ['c']]]
          ]
        );
        $reason = '';

        $this->assertTrue($rules->shouldAllow(['a', 'b', 'c'], null, $reason));
        $this->assertTrue($rules->shouldAllow(['d', 'a', 'b', 'c'], null, $reason));

        $this->assertFalse($rules->shouldAllow(['a', 'b'], null, $reason));
        $this->assertFalse($rules->shouldAllow(['b', 'c'], null, $reason));
        $this->assertFalse($rules->shouldAllow(['a'], null, $reason));
        $this->assertFalse($rules->shouldAllow(['b'], null, $reason));
    }

    public function testNestedDeny()
    {
        $rules = new Rules(
          [
            'deny' => ['a' => ['b' => ['c']]]
          ]
        );
        $reason = '';

        $this->assertFalse($rules->shouldAllow(['a', 'b', 'c'], null, $reason));

        $this->assertTrue($rules->shouldAllow(['a', 'b', 'c', 'd'], null, $reason));
        $this->assertTrue($rules->shouldAllow(['a', 'b'], null, $reason));
        $this->assertTrue($rules->shouldAllow(['b', 'c'], null, $reason));
    }

    public function testMatcher()
    {
        $rules = new Rules(
          [
            'allow' => ['a' => ['b' => new TestMatcher('hello')]]
          ]
        );

        $reason = '';
        $this->assertTrue($rules->shouldAllow(['k', 'a', 'b'], 'hello', $reason));
        $this->assertFalse($rules->shouldAllow(['k', 'a', 'b'], 'world', $reason));
    }
}

class TestMatcher implements Matcher
{
    /**
     * @var string
     */
    private $value;

    /**
     * TestMatcher constructor.
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function match(array $path, $context, string &$reason): bool
    {
        return $context === $this->value;
    }
}