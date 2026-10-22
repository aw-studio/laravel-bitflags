# Laravel Bitflags

Store and receive bitflags from a single database column.

## Installation

```bash
composer require aw-studio/laravel-bitflags
```

## Usage

Imagine you want to store multiple status flags in a single `status(int)` column of your `Email` model.
This can be achieved using bitwise operations to create a representative `bitmask`.
In order to enable bitwise operations bitflags `MUST` all be powers of two (1,2,4,8,16 …).
You should also make shure to properly cast the column as `Bitflags::class`.

```php
class Email extends Model
{
    // Email status flags, all powers of 2
    public const SENT = 1;
    public const RECEIVED = 2;
    public const SEEN = 4;
    public const READ = 8;

    protected $fillable = ['status'];

    public $casts = [
        'status' => Bitflags::class
    ];
}
```

### Adding a flag to a bitmask

Adding a `bitflag` to a bitmask can be achieved using the `addBitflag()` helper:

```php
public function markRead()
{
    $this->update([
        'status' => addBitflag(self::READ, $this->status)
    ]);
}
```

You can also add multiple flags at once:

```php
$this->update([
    'status' => addBitflag([self::READ, self::SEEN], $this->status)
]);
```

### Removing a flag from a bitmask

Removing a `bitflag` from a bitmask can be achieved using the `removeBitflag()` helper:

```php
public function markUnread()
{
    $this->update([
        'status' => removeBitflag(self::READ, $this->status)
    ]);
}
```

Remove multiple flags at once:

```php
$this->update([
    'status' => removeBitflag([self::READ, self::SEEN], $this->status)
]);
```

### Query bitflags

To check if bitflags are included in a bitmask you may use the following query methods:

```php
public function scopeRead($query)
{
    return $this->whereBitflag('status', self::READ);
}
public function scopeUnread($query)
{
    return $this->whereBitflagNot('status', self::READ);
}
public function scopeSeenOrRead($query)
{
    return $this->whereBitflagIn('status', [self::READ, self::SEEN]);
}
public function scopeSeenAndRead($query)
{
    return $this->whereBitflags('status', [self::READ, self::SEEN]);
}
```

## Accessors

In order to get single flag attributes you can prepare accessors as follows:

```php
protected $appends = ['read'];

public function getReadAttribute()
{
    return inBitmask(self::READ, $this->status);
}
```
