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

A parenthesised expression is a head like any other, so
`(new Mapper($config))->withDefaults()->build()->result()` breaks over lines
the same way.

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

## MultiLineCallEndsStatement

A call whose arguments span multiple lines must end its statement. Not
auto-fixable.

```xml
<rule ref="Maarheeze.Formatting.MultiLineCallEndsStatement"/>
```

A statement may break its arguments or break its chain, never both. Once an
argument list is spread over lines, its closing `])` sits at the statement's
indent and anything chained onto it has nowhere to go: a broken chain lands in
the argument list's own column, and an unbroken one hides behind the bracket.

```php
// before
Livewire::test(CompanyPanel::class, [
    'game' => $gameScenario->game(),
    'company' => Fake::company(),
])->assertSee('Sell shares');

// after
$testable = Livewire::test(CompanyPanel::class, [
    'game' => $gameScenario->game(),
    'company' => Fake::company(),
]);

$testable->assertSee('Sell shares');
```

The rule holds wherever the call sits in the chain, and breaking the chain does
not settle it — that is the point:

```php
// before
$rows = $query
    ->where([
        'game_id' => $gameId,
        'active' => true,
    ])
    ->get();

// after
$conditions = [
    'game_id' => $gameId,
    'active' => true,
];

$rows = $query->where($conditions)->get();
```

The last call of a statement is never reported: nothing is chained onto it, so
it ends the statement by definition. That is what makes the fix work, and it is
also why the sniff only reports — hoisting the call into a variable means
naming it, which no fixer can do.

### configuration

`allowClosureArguments` exempts a call that spans lines only because of a
closure, arrow function, `match` or heredoc it is passed, and defaults to
`true`:

```php
// accepted by default, reported with allowClosureArguments = false
$active = $players
    ->filter(static function (Player $player): bool {
        return $player->is_active;
    })
    ->values();
```

Such a call is multi-line because of a body that is written over several lines
by nature, not because its argument list was wrapped, and hoisting it into a
variable reads worse. A wrapped argument list is still reported, closure or
not:

```php
// reported either way
$found = $query
    ->where(
        'active',
        true,
    )
    ->get();
```

```xml
<rule ref="Maarheeze.Formatting.MultiLineCallEndsStatement">
    <properties>
        <property name="allowClosureArguments" value="false"/>
    </properties>
</rule>
```

### together with MultiLineMethodChaining

The two are independent and both are on by default. Where this sniff reports,
`MultiLineMethodChaining` is usually silent already: a head that is broken up
is its documented exemption. Once a violation is fixed, what is left of the
chain is judged by that sniff on its own terms.
