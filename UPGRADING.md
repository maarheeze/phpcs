# Upgrade guide

Breaking changes per major version, newest first.

## 2.x to 3.0

The coding standard was renamed from `maarheeze` to `Maarheeze`. Update the
reference in your `phpcs.xml.dist`:

```diff
-    <rule ref="maarheeze"/>
+    <rule ref="Maarheeze"/>
```

## 1.x to 2.0

The package now requires PHP_CodeSniffer 4.0 — upgrade your runtime first, then
the package.