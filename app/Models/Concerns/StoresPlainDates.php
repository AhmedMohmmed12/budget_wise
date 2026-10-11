<?php

namespace App\Models\Concerns;

use Carbon\CarbonImmutable;

/**
 * Stores the attributes listed in $plainDates as Y-m-d (Eloquent's date cast
 * would write "Y-m-d H:i:s", which breaks range queries on SQLite).
 */
trait StoresPlainDates
{
    public function setAttribute($key, $value)
    {
        if ($value !== null && in_array($key, $this->plainDates, true)) {
            $this->attributes[$key] = CarbonImmutable::parse($value)->toDateString();

            return $this;
        }

        return parent::setAttribute($key, $value);
    }
}
