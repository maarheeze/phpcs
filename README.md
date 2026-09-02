# phpcs

extension of [PHPCSStandards/PHP_CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer/)

Two separate things live in this package, take either or both:

- **custom sniffs** that drop into any ruleset, whatever standard you already use
- **an opinionated standard** — `Maarheeze` — PSR-12 plus a selection of
  Generic, Squiz and Slevomat sniffs, and the custom sniffs

## installation

```
composer require maarheeze/phpcs --dev
```

The Composer plugin registers everything with PHP_CodeSniffer, so both the
sniffs and the standard can be referenced by name from here on. There is
nothing else to set up.

## the custom sniffs

| sniff | description |
| --- | --- |
| [`Maarheeze.Formatting.MultiLineMethodChaining`](docs/formatting.md#multilinemethodchaining) | one member per line in a method chain |

They are referenced like any other sniff, and nothing else from this package
comes with them. What each one reports and which properties it accepts is
documented in [docs](docs).

## the opinionated standard

`Maarheeze` is one reference that stands in for a whole ruleset. It builds on
PSR-12, adds a selection of Generic, Squiz and Slevomat sniffs — strict types,
trailing commas everywhere, imports sorted and pruned, short ternaries, no Yoda
comparisons, a 90 character soft line limit — and brings in the custom sniffs
above. The point is that the choices are settled here, so a project does not
have to argue them out again:

```xml
<rule ref="Maarheeze"/>
```

If the project has no ruleset yet, create a `phpcs.xml.dist` in its root:

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

And run it:

```
php vendor/bin/phpcs
```

### editing the ruleset

Take the standard whole, or bend it where you disagree. It is an ordinary
standard, so everything a ruleset can normally do applies.

Drop a sniff you do not want:

```xml
<rule ref="Maarheeze">
    <exclude name="SlevomatCodingStandard.ControlStructures.DisallowYodaComparison"/>
</rule>
```

Or override the properties of one. That is a second, separate `<rule>` next to
the first — a `<rule>` cannot be nested in another, and referencing a sniff the
standard already includes configures it rather than adding it twice:

```xml
<rule ref="Maarheeze"/>

<rule ref="Maarheeze.Formatting.MultiLineMethodChaining">
    <properties>
        <property name="minimumCalls" value="3"/>
    </properties>
</rule>
```
