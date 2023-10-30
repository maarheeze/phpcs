# phpcs

extension of [squizlabs/PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer/), but with a custom ruleset

## installation

- require the package
```
composer require maarheeze/phpcs --dev
```

- create `phpcs.xml.dist` file in the root of the project as a starting point:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<rule name="phpcs">
    <description>phpcs</description>
    <arg value="sp"/>
    <arg name="colors"/>
    <arg name="cache" value="/tmp/.phpcs.cache"/>
    <arg name="extensions" value="php"/>

    <file>.</file>
    <exclude-pattern>./node_modules</exclude-pattern>
    <exclude-pattern>./vendor</exclude-pattern>

    <rule ref="maarheeze"/>
</rule>
```

### optional

- add `phpcs.xml` to the `.gitignore` in the root of the project (to allow local override)
- add folders to exclude

## usage

`php vendor/bin/phpcs` 
