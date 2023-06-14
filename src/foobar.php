<?php

namespace catalyst\foobar;

class FooBar
{
    protected static function getIteratorString(int $n): string {
        $output = '';

        // The next two lines are carfully written to be able to produce foo, bar, or foobar
        if ($n % 3 == 0) $output .= 'foo';
        if ($n % 5 == 0) $output .= 'bar';

        // If not foo, bar, or foobar use $n
        if ($output == '') $output = (string)$n;

        return $output;
    }

    public static function iterate(int $start, int $finish): void {
        if ($start > $finish) {
            echo "\$finish($finish) can't be smaller than \$start($start)\r\n\r\n";
            return;
        }
        
        for ($n = $start; $n <= $finish; $n++) {
            echo ($n > $start ? ', ' : '') . self::getIteratorString($n);
        }
        echo "\r\n\r\n";
        return;
    }

    public static function init(): void {
        self::iterate(1, 100);

    }
}
FooBar::init();

