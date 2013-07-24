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

require_once 'vendor/autoload.php';

use MarkWilson\VerbalExpression;

$innerExpression = new VerbalExpression();
$innerExpression->word();

$outerExpression = new VerbalExpression();
$outerExpression->startOfLine()
                ->then($innerExpression)
                ->then($innerExpression)
                ->endOfLine();

// returns ^(\w+)(\w+)$
$outerExpression->compile();
````



  [1]: https://github.com/jehna/VerbalExpressions "jehna/VerbalExpressions"
