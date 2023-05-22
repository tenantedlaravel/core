<?php
declare(strict_types=1);

namespace Tenanted\Core\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Array Overrides
 *
 * Helper class for dealing with overriding of arrays.
 */
final class ArrayOverrides
{
    /**
     * @var string[]
     */
    private array $allow;

    /**
     * @var string[]
     */
    private array $disallow;

    /**
     * @param string[]|empty $allow
     * @param string[]|empty $disallow
     */
    public function __construct(array $allow = ['*'], array $disallow = [])
    {
        $this->allow    = $allow;
        $this->disallow = $disallow;
    }

    /**
     * Get the allowed patterns
     *
     * @return array|string[]
     */
    public function allow(): array
    {
        return $this->allow;
    }

    /**
     * Get the disallowed patterns
     *
     * @return array|string[]
     */
    public function disallow(): array
    {
        return $this->disallow;
    }

    /**
     * Check if a key matches
     *
     * @param string $key
     * @param bool   $allow
     *
     * @return bool
     */
    protected function check(string $key, bool $allow): bool
    {
        $these = $allow ? $this->allow() : $this->disallow();
        $those = $allow ? $this->disallow() : $this->allow();

        if (empty($these)) {
            return empty($those) || ! Str::is($those, $key);
        }

        return Str::is($these, $key);
    }

    /**
     * Check if key is allowed
     *
     * @param string $key
     *
     * @return bool
     */
    public function allows(string $key): bool
    {
        return $this->check($key, true);
    }

    /**
     * Check if a key is disallowed
     *
     * @param string $key
     *
     * @return bool
     */
    public function disallows(string $key): bool
    {
        return $this->check($key, false);
    }

    /**
     * Clean the data by filtering through the allows and disallows
     *
     * @param array $data
     * @param bool  $undot
     *
     * @return array
     */
    public function clean(array $data, bool $undot = true): array
    {
        $data = Arr::dot($data);

        foreach ($data as $key => $value) {
            if ($this->disallows($key)) {
                unset($data[$key]);
            }

            if (! $this->allows($key)) {
                unset($data[$key]);
            }
        }

        return $undot ? Arr::undot($data) : $data;
    }

    /**
     * Clean an array and merge it into another
     *
     * @param array $original
     * @param array $new
     *
     * @return array
     */
    public function override(array $original, array $new): array
    {
        return Arr::undot(
            array_merge(Arr::dot($original), $this->clean($new, false))
        );
    }
}