# Formatting

## MultiLineMethodChaining

One member per line in a method chain. Auto-fixable.

```xml
<rule ref="Maarheeze.Formatting.MultiLineMethodChaining"/>
```

A chain's head is its subject — a bare variable plus its first member, or a
standalone leading call when there is no variable to attach it to. The head
stays on the statement's first line; every further `->` / `?->` starts its own
line, indented four spaces past the indent of the head's line.

```php
// before
$user = $this->service->find($id)->refresh()->toArray();

// after
$user = $this->service
    ->find($id)
    ->refresh()
    ->toArray();
```

A chain breaks when three or more of its members are calls. Anything else is
left as written:

```php
// two member calls — under the threshold
$result = $query->where('active', true)->first();

// no calls at all — property fetches are members, but never count
$value = $game->firstPlayer->token->value;

// the head is already broken over several lines
$response = $kernel->handle(
    $request,
)->send();
```

A chain broken up more than the rule requires is accepted as it is.

### configuration

`minimumCalls` sets how many member calls a chain needs before it has to
break, and defaults to `3`:

```xml
<rule ref="Maarheeze.Formatting.MultiLineMethodChaining">
    <properties>
        <property name="minimumCalls" value="2"/>
    </properties>
</rule>
```

Under the opinionated standard, put that same element next to
`<rule ref="Maarheeze"/>`. It does not add a second sniff; it configures the
one the standard already brings in.
