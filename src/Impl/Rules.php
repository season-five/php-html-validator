<?php


namespace SeasonFive\HtmlValidator\Impl;


use InvalidArgumentException;
use SeasonFive\HtmlValidator\Matcher;

/**
 * Validation rules. Used both for tags and attributes.
 */
class Rules
{
    const ALLOW = 'allow';
    const DENY = 'deny';
    const OVERRIDES = 'overrides';

    const ALLOW_ALL = [self::DENY => []];

    private $allowExplicitly = false;
    private $rules = [];
    private $overrides = [];

    /**
     * Creates a rule set from the given `$config`.
     *
     * @param array $config the validation rule
     */
    public function __construct(array $config)
    {
        $hasAllow = key_exists(self::ALLOW, $config);
        $hasDeny = key_exists(self::DENY, $config);

        if (($hasAllow && $hasDeny) || (!$hasAllow && !$hasDeny)) {
            throw new InvalidArgumentException('either allow or deny should be specified');
        }

        if ($hasAllow) {
            $this->allowExplicitly = true;
            $this->prepareRules($this->rules, $config[self::ALLOW]);
        } else {
            $this->allowExplicitly = false;
            $this->prepareRules($this->rules, $config[self::DENY]);
        }

        if (key_exists(self::OVERRIDES, $config)) {
            $this->prepareRules($this->overrides, $config[self::OVERRIDES]);
        }
    }

    /**
     * Checks if the given `$path` is allowed based on this rule.
     *
     * @param array $path the full path to the item to check
     * @param mixed $context additional data associated with the item represented by `$path`
     * @param string &$reason assigned the reason for the check on return
     * @return bool `true` if the item at the given `$path` should be allowed. `false` otherwise
     */
    public function shouldAllow(array $path, $context, string &$reason): bool
    {
        if ($this->matchAgainst($this->overrides, $path, $context, $reason)) {
            return !$this->allowExplicitly;
        }
        if ($this->matchAgainst($this->rules, $path, $context, $reason)) {
            return $this->allowExplicitly;
        }
        return !$this->allowExplicitly;
    }

    private function matchAgainst(array $rules, array $path, $context, string &$reason): bool
    {
        for (end($path); $current = current($path); prev($path)) {
            if (key_exists($current, $rules)) {
                $rules = $rules[$current];
            } else {
                break;
            }
        }
        if (!key_exists(0, $rules)) {
            return false;
        }
        if (is_null($rules[0])) {
            return true;
        }
        return $rules[0]->match($path, $context, $reason);
    }

    /**
     * Prepares rules from the given `$config`.
     *
     * @param array &$target the target array to put the prepared rules
     * @param array $config a set of rules
     * @param array &$path path to the current rule set in case the `$config` is hierarchical
     *
     * @throws InvalidArgumentException when either key or value is of unexpected type
     */
    private function prepareRules(array &$target, array $config, array &$path = [])
    {
        foreach ($config as $key => $value) {
            if (is_int($key)) {
                $path[] = $value;
                self::putValueToPath($target, $path, null);
                array_pop($path);
            } elseif (is_string($key)) {
                $path[] = $key;
                if (is_array($value)) {
                    $this->prepareRules($target, $value, $path);
                } elseif ($value instanceof Matcher) {
                    self::putValueToPath($target, $path, $value);
                } else {
                    $at = join('/', $path);
                    throw new InvalidArgumentException("invalid value at $at");
                }
                array_pop($path);
            } else {
                $at = join('/', $path);
                throw new InvalidArgumentException("invalid key at $at");
            }
        }
    }

    private static function putValueToPath(array &$target, array $path, $value)
    {
        $currentTarget = &$target;
        for (end($path); $current = current($path); prev($path)) {
            if (!isset($currentTarget[$current])) {
                $currentTarget[$current] = [];
            }
            $currentTarget = &$currentTarget[$current];
        }
        $currentTarget[0] = $value;
    }
}