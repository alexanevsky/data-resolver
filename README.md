# How to use
## Simple example

Configure resolver:

```php
use Alexanevsky\DataResolver\Resolver;

$resolver = new Resolver();
$resolver->define('foo', 'string');
$resolver->define('bar', 'int');
```

Then try to resolve data:

```php
$data = [
    'qwerty' => 'Hello World',
    'bar' => '12345'
];

$result = $resolver->resolve($data);

var_dump($result->toArray());
```

And get result:

```json
{
    "foo": "",
    "bar": 12345
}
```

Detailed documentation will be added later...