<?php

namespace AwStudio\Bitflags\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class Bitflags implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return array
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        if (is_null($value)) {
            return [];
        }

        $bits = array_reverse(str_split(decbin((int) $value)));
        $mask = [];
        foreach ($bits as $i => $bit) {
            if ($bit != '0') {
                $mask[] = 1 << $i;
            }
        }
        return $mask;
    }
    
    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  array  $value
     * @param  array  $attributes
     * @return int|null
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_array($value)) {
            $bitmask = 0;
            foreach ($value as $bit) {
                if (!$this->isPowerOfTwo($bit)) {
                    throw new InvalidArgumentException($bit . ' is not a power of two.');
                }
                $bitmask = $bitmask | $bit;
            }

            return $bitmask;
        }
        
        return $value;
    }

    public function isPowerOfTwo(int $number): bool
    {
        return ($number & ($number - 1)) == 0;
    }
}
