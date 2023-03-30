<?php

namespace catalyst\foobar;

class FooBar
{
	protected static function getSeperator(int $n): string {
		// Separate the iterations correctly
		return $n > 1 ? ', ' : '';
	}

	protected static function getIteratorString(int $n): string {
		$output = '';
		
		// The next two lines are carfully written to be able to produce foo, bar, or foobar
		if ($n % 3 == 0) $output .= 'foo';
		if ($n % 5 == 0) $output .= 'bar';

		// If not foo, bar, or foobar use $n
		if ($output == '') $output = (string)$n;

		return self::getSeperator($n) . $output;
	}

	protected static function iterate(): void {
		for ($n = 1; $n <= 100; $n++) {
			echo self::getIteratorString($n);
		}
	}

	public static function init(): void {
		self::iterate();

	}
}

FooBar::init();

