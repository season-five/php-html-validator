# HTML Validator

A simple rule-based HTML validator that actually parses input HTML
with [an HTML5 parser](https://github.com/Masterminds/html5-php).

Usually, taking HTML as input in the server-side is generally not a good idea, and in most cases, what you want is an
HTML sanitizer. However, if you should, this HTML validator should come in handy.

[![Latest Version](https://img.shields.io/github/release/season-five/php-html-validator.svg?style=flat-square)](https://github.com/season-five/php-html-validator/releases)
[![Build Status](https://img.shields.io/github/workflow/status/season-five/php-html-validator/CI?label=ci%20build&style=flat-square)](https://github.com/season-five/php-html-validator/actions?query=workflow%3ACI)
[![Total Downloads](https://img.shields.io/packagist/dt/seasonfive/html-validator.svg?style=flat-square)](https://packagist.org/packages/seasonfive/html-validator)

# Installation

Add the 'seasonfive/html-validator' to your `composer.json` file as follows:

```json
{
  "require": {
    "seasonfive/html-validator": "^1.0"
  }
}
```

or invoke `composer require` command (assuming `composer` binary is available in the path) as follows:

```bash
$ composer require seasonfive/html-validator
```

# Usage

```php
$validator = new Validator([
    'tags' => [
        'deny' => ['script', 'svg', 'img'],
        'overrides' => ['a' => ['img']]
    ],
    'attrs' => [
        'allow' => ['href', 'div' => ['data-role']]
    ]
]);

if ($validator->validate('<a href="http://example.com">link</a>')) {
    print "Valid!!\n";
}
```

## Errors

More detailed reports on what errors are encountered, if any, may be obtained if you pass a variable as follows:

```php
if (!$validator->validate('<a href="http://example.com">link</a>', false, $errors)) {
    var_dump($errors);
}
```

Above, `$errors` is passed an array containing `ValidationError`s and `ParseError`. `ValidationError` designates where
in the HTML an error is occurred, and `ParseError` where parsing failed.

## Disabling Fail Fast

In most cases, you would want `Validator` to report error as soon as it pinpoints the first one and stop further
validation. However, it is also possible to continue to parse the input HTML and report all the errors at once as shown
in the example in `Errors` section above, where the second argument to `validate` call is set to `false`. If not
specified, `Validator` always fails fast.

# Writing Rules

Rules for tags and attributes are specified in an array and given to the constructor of `Validator`. The rule array
has `tags` and `attrs` keys, which contain rules for tags and attributes respectively. Note that any of them may be
omitted meaning nothing is denied.

## Allow/Deny

`tags` and `attrs` have another array as their values, the formats of which are identical. In the array, either `allow`
or `deny` key may be specified. `allow` means only those specified explicitly allowed and all the others are denied (
that is, invalid). Conversely, `deny` means only explicitly specified ones are denied and all the others allowed.

## Overrides

Optionally, `overrides` may be specified to override what is specified under either `allow` or `deny`. For instance,
when `allow` is specified, `overrides` lists what must be denied. Usually more specific rules than those in `allow`
or `deny` are listed there.

## Tags and Attributes

In each of `allow`, `deny`, and `overrides`, tags and attributes are listed, where they can be nested as follows:

```php
[
    'tags' => [
        'allow' => ['script', 'div' => ['p', 'a']]
     ],
    'attrs' => [
        'deny' => ['onmouseover', 'onclick'],
        'overrides' => ['a' => ['onclick'], 'html' => ['body' => ['div' => 'onmouseover']]]    
    ]
]
```

In `tags` array, the leaf (the deepest in the nested arrays) strings are tag names, and in `attrs`, attribute names.
Nesting means the rule applies to the tag or attribute only if it is nested the same way as the rule. For example, in
the above example, `'html' => ['body' => ['div' => 'onmouseover']` means the rule is effective only to `onmouseover`
if it is specified to a `div` tag directly in a `body`, in turn, in an `html` tag.

## Matcher

Instead of a string value, `Matcher` instance may be given as follows:

```php
[
    'tags' => [
        'allow' => ['script', 'div' => new TypeMatcher()]
    ]
]
```

In the above example, `TypeMatcher` implements `Matcher` interface, and it may implement more sophisticated matching
rule. The `Matcher` implements the following function, which gets called to see if a tag or attribute is actually
matched, and the rule should be applied or not:

```php
public function match(array $path, $context, string &$reason): bool
```

`$context` is given a `TagContext` for tags, and string values for attributes.
