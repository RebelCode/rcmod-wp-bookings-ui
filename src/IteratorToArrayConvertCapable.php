<?php

namespace RebelCode\Bookings\WordPress\Module;

use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use stdClass;
use Traversable;

/**
 * Trait IteratorToArrayConvertCapable, adds ability to convert an iterator to an array.
 *
 * @since [*next-version*]
 */
trait IteratorToArrayConvertCapable
{
    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /**
     * Convert an iterator to an array.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $iterator    The object to convert.
     * @param callable                   $transformer Optional function that applies to value.
     *
     * @throws InvalidArgumentException If $iterator is not iterable.
     *
     * @return array Result of iterator to array transform.
     */
    protected function _iteratorToArrayRecursive($iterator, $transformer = null)
    {
        $iterator = $this->_normalizeIterable($iterator);
        $array    = [];
        foreach ($iterator as $key => $value) {
            if ($value instanceof Traversable || is_array($value) || $value instanceof stdClass) {
                $array[$key] = $this->_iteratorToArrayRecursive($value, $transformer);
                continue;
            }
            $array[$key] = $transformer && is_callable($transformer) ? $transformer($value) : $value;
        }

        return $array;
    }
}
