# Guide use

## 1. Use with composer

```php
require 'path/to/vendor/autoload.php';

use Mateodioev\Translate;
$tr = new Translate;
```

### Google

```php
$res = $tr->google('Your text to translate', 'source_lang', 'target_lang');
```

### Yandex

```php
$key = 'YOUR_API_KEY'; // see: https://translate.yandex.com/developers/keys
$res = $tr->yandex($key, 'Your text to translate', 'source_lang', 'target_lang'),
```

## Another formats

```php
$tr->setText('Hello world!')->setInputLang('en')->setOutputLang('es');

$res = $tr->yandex($key);
$res = $tr->google();
# Use any of the above methods
```

## Get translate text

```php
$tr->getText();
```

## Get lang name

```php
$dir = 'input or output';
$tr->getLangName($dir);
```
