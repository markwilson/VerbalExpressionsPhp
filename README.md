# VerbalExpressionsPhp

PHP port of [jehna/VerbalExpressions][1].

## Installation

### Composer

Add to composer.json:-

```` json
{
    "require": {
        ...,
        "markwilson/verbal-expressions-php": "dev-master"
    }
}
````

## Example usage

```` php
<?php

require_once 'vendor/autoload.php';

use MarkWilson\VerbalExpression;
use MarkWilson\VerbalExpression\Matcher;

// initialise verbal expression instance
$verbalExpression = new VerbalExpression();

// URL matcher
$verbalExpression->startOfLine()
                 ->then('http')
                 ->maybe('s')
                 ->then('://')
                 ->maybe('www.')
                 ->anythingBut(' ')
                 ->endOfLine();

// compile expression - returns ^(http)(s)?(\:\/\/)(www\.)?([^\ ]*)$
$verbalExpression->compile();

// perform match
preg_match($verbalExpression, 'http://www.google.com'); // returns 1
// or
$matcher = new Matcher();
$matcher->isMatch($verbalExpression, 'http://www.google.com'); // returns true
````

## Nesting expressions

```` php
<?php

$innerExpression = new VerbalExpression();
$innerExpression->word();

$outerExpression = new VerbalExpression();
$outerExpression->startOfLine()
                ->find($innerExpression)
                ->then($innerExpression)
                ->endOfLine();

// returns ^(\w+)(\w+)$
$outerExpression->compile();
````

## Disable sub pattern capturing

By default, sub patterns are captured and will be returned in the matches array.

```` php
<?php

// disable sub pattern capture
$verbalExpression->disableSubPatternCapture()->word(); // (?:\w+)
// or
$verbalExpression->word(false); // (?:\w+)
````

Disabling this will only affect subsequent additions to the expression; any already added will be unaffected.
This allows for disabling and enabling in groups.

```` php
<?php

// equivalent to (\w+)(?:\w+)(?:\w+)(\w+)
$verbalExpression->word()
                 ->disableSubPatternCapture()
                 ->word()
                 ->word()
                 ->enableSubPatternCapture()
                 ->word();
````

  [1]: https://github.com/jehna/VerbalExpressions "jehna/VerbalExpressions"
