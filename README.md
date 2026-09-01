# phpcs

extension of [PHPCSStandards/PHP_CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer/)

Two separate things live in this package, take either or both:

- **a sniff** — `Maarheeze.Formatting.MultiLineMethodChaining` — that drops into
  any ruleset, whatever standard you already use
- **an opinionated standard** — `Maarheeze` — PSR-12 plus a selection of
  Generic, Squiz and Slevomat sniffs, and the sniff above

## installation

```
composer require maarheeze/phpcs --dev
```

The Composer plugin registers the standard with PHP_CodeSniffer, so both of the
references below work straight away.

## the sniff on its own

Reference it from your own ruleset, next to whatever else you run:

```xml
<rule ref="Maarheeze.Formatting.MultiLineMethodChaining"/>
```

Nothing else from this package comes with it.

### what it does

One member per line in a method chain. Auto-fixable.

A chain's head is its subject — a bare variable plus its first member, or a
standalone leading call when there is no variable to attach it to. The head
stays on the statement's first line; every further `->` / `?->` starts its own
line, indented four spaces past the indent of the head's line.

```php
// before
$user = $this->service->find($id)->toArray();

// after
$user = $this->service
    ->find($id)
    ->toArray();
```

It applies to chains of two or more calls. Property fetches are members and
break along with the rest, but do not count toward that threshold, so a chain
of pure fetches is left alone:

```php
$value = $game->firstPlayer->token->value;
```

A chain broken up more than the rule requires is accepted as it is.

## the opinionated standard

Pulls in PSR-12, the Generic / Squiz / Slevomat selection, and the sniff above:

```xml
<rule ref="Maarheeze"/>
```

- create a `phpcs.xml.dist` file in the root of the project as a starting point:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<ruleset name="phpcs">
    <description>phpcs</description>
    <arg value="sp"/>
    <arg name="colors"/>
    <arg name="cache" value="/tmp/.phpcs.cache"/>
    <arg name="extensions" value="php"/>

    <file>.</file>
    <exclude-pattern>./node_modules</exclude-pattern>
    <exclude-pattern>./vendor</exclude-pattern>

    <rule ref="Maarheeze"/>
</ruleset>
```

### optional

- add `phpcs.xml` to the `.gitignore` in the root of the project (to allow local override)
- add folders to exclude

## usage

`php vendor/bin/phpcs` 
