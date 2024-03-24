<?php namespace x\markdown__embed;

function join(array $blocks, callable $fn) {
    foreach ($blocks as &$block) {
        if (false === $block[1]) {
            $block = $block[0];
            continue;
        }
        $block = $block[0];
        if ('>' === $block[0]) {
            $parts = \explode("\n", $block);
            foreach ($parts as $k => $v) {
                if ('> ' === \substr($v, 0, 2)) {
                    $parts[$k] = \substr($v, 2);
                    continue;
                }
                if ('>' === $v[0]) {
                    $parts[$k] = \substr($v, 1);
                    continue;
                }
            }
            $block = join(split(\implode("\n", $parts)), $fn);
            $parts = \explode("\n", $block);
            foreach ($parts as $k => $v) {
                $parts[$k] = ("" === $v ? '>' : '> ' . $v);
            }
            $block = \implode("\n", $parts);
            continue;
        }
        if (false !== \strpos('*+-', $block[0]) && false !== \strpos(" \t", $block[1] ?? "")) {
            $parts = \explode("\n", $block);
            $n = 1 + \strspn(\substr($block, 1), " \t");
            $fix = \substr($block, 0, $n);
            foreach ($parts as $k => $v) {
                if (0 === $k || \strspn($v, " \t") >= $n) {
                    $parts[$k] = \substr($v, $n);
                    continue;
                }
            }
            $block = join(split(\implode("\n", $parts)), $fn);
            $parts = \explode("\n", $block);
            foreach ($parts as $k => $v) {
                if (0 === $k) {
                    $parts[$k] = $fix . $v;
                    continue;
                }
                $parts[$k] = ("" === $v ? "" : \str_repeat(' ', $n) . $v);
            }
            $block = \implode("\n", $parts);
            continue;
        }
        $n = \strspn($block, '0123456789');
        if ($n <= 9 && false !== \strpos(').', \substr($block, $n, 1)) && false !== \strpos(" \t", \substr($block, $n + 1, 1))) {
            $parts = \explode("\n", $block);
            $n = $n + 1 + \strspn(\substr($block, $n + 1), " \t");
            $fix = \substr($block, 0, $n);
            foreach ($parts as $k => $v) {
                if (0 === $k || \strspn($v, " \t") >= $n) {
                    $parts[$k] = \substr($v, $n);
                    continue;
                }
            }
            $block = join(split(\implode("\n", $parts)), $fn);
            $parts = \explode("\n", $block);
            foreach ($parts as $k => $v) {
                if (0 === $k) {
                    $parts[$k] = $fix . $v;
                    continue;
                }
                $parts[$k] = ("" === $v ? "" : \str_repeat(' ', $n) . $v);
            }
            $block = \implode("\n", $parts);
            continue;
        }
        $test = \trim($block);
        if ('<' === $test[0] && '>' === \substr($test, -1) && false !== \strpos($test, ':')) {
            $n = \strspn($block, " \t");
            $r = [\substr($block, $n), $fix = \substr($block, 0, $n)];
            $block = \call_user_func($fn, ...$r);
            $parts = \explode("\n", $block);
            foreach ($parts as $k => $v) {
                if ("" === $v) {
                    continue;
                }
                $parts[$k] = $fix . $v;
            }
            $block = \implode("\n", $parts);
        }
    }
    unset($block);
    return \implode("\n", $blocks);
}

function split(string $content) {
    $block = -1;
    $blocks = [];
    $rows = \explode("\n", $content);
    foreach ($rows as $row) {
        // TODO: Keep the tab character(s) as-is!
        while (false !== ($before = \strstr($row, "\t", true))) {
            $v = \strlen($before);
            $row = $before . \str_repeat(' ', 4 - $v % 4) . \substr($row, $v + 1);
        }
        $dent = \strspn($row, ' ');
        if ($prev = $blocks[$block][0] ?? 0) {
            $n = \strspn($prev, '#');
            // Previous block is a header block?
            if ($n > 0 && $n < 7 && false !== \strpos(" \t", \substr($prev . ' ', $n, 1))) {
                $blocks[++$block] = [$row, "" !== $row];
                continue;
            }
            // Previous block is an element block?
            if ($row && '<' === $row[0]) {
                if (\preg_match('/^<[a-z][a-z\d-]*(\s(?>"[^"]*"|\'[^\']*\'|[^>])*)?>(\n|$)/i', $prev)) {
                    $blocks[$block][0] .= "\n" . $row;
                    continue;
                }
                $blocks[++$block] = [$row, true];
                continue;
            }
            // Previous block is a quote block and current block is also a quote block?
            if ($row && '>' === $row[0] && '>' === $prev[0]) {
                $blocks[$block][0] .= "\n" . $row;
                continue;
            }
            // Is in a code block?
            if (false !== \strpos('`~', $prev[0]) && ($n = \strspn($prev, $prev[0])) >= 3) {
                $test = \strstr($prev, "\n", true) ?: $prev;
                // Character ‘`’ cannot exist in the info string if code block fence uses ‘`’ character(s)
                if ('`' === $prev[0] && false !== \strpos(\substr($test, $n), '`')) {
                    $blocks[++$block] = [$row, "" !== $row];
                    continue;
                }
                // End of the code block?
                if ($row === \str_repeat($prev[0], $n)) {
                    $blocks[$block++][0] .= "\n" . $row;
                    continue;
                }
                // Continue the code block…
                $blocks[$block][0] .= "\n" . $row;
                $blocks[$block][1] = false;
                continue;
            }
            // Previous block is a list block?
            if (false !== \strpos('*+-', $prev[0])) {
                // End of the list block?
                if ('-' === $prev || false !== \strpos(" \t", $prev[1]) && "" !== $row && $dent < 2) {
                    if ("\n" === \substr($prev, -1)) {
                        $blocks[$block][0] = \substr($blocks[$block][0], 0, -1);
                        $blocks[++$block] = ["", false];
                    }
                    $blocks[++$block] = [$row, "" !== $row];
                    continue;
                }
                // Previous block is a horizontal rule?
                $test = \strtr($prev, [
                    "\t" => "",
                    ' ' => ""
                ]);
                if (\strlen($test) === ($n = \strspn($test, $test[0])) && $n > 2) {
                    $blocks[++$block] = [$row, "" !== $row];
                    continue;
                }
                // Continue the list block…
                $blocks[$block][0] .= "\n" . $row;
                continue;
            }
            // Previous block is a horizontal rule?
            if ('_' === $prev[0]) {
                $test = \strtr($prev, [
                    "\t" => "",
                    ' ' => ""
                ]);
                if (\strlen($test) === ($n = \strspn($test, $test[0])) && $n > 2) {
                    $blocks[++$block] = [$row, "" !== $row];
                    continue;
                }
                // Previous block is not a horizontal rule…
                $blocks[$block][0] .= "\n" . $row;
                continue;
            }
            // Previous block is a list block?
            $n = \strspn($prev, '0123456789');
            if ($n > 0 && $n < 10 && false !== \strpos(').', \substr($prev, $n, 1))) {
                if ($n + 1 === \strlen($prev) || false !== \strpos(" \t", \substr($prev, $n + 1, 1))) {
                    // End of the list block?
                    if ("" !== $row && $dent < $n + 2) {
                        if ("\n" === \substr($prev, -1)) {
                            $blocks[$block][0] = \substr($blocks[$block][0], 0, -1);
                            $blocks[++$block] = ["", false];
                        }
                        $blocks[++$block] = [$row, "" !== $row];
                        continue;
                    }
                }
                // Continue the list block…
                $blocks[$block][0] .= "\n" . $row;
                continue;
            }
            // Is in code block?
            if (\strspn($prev, ' ') >= 4) {
                // End of the code block?
                if ("" !== $row && $dent < 4) {
                    if ("\n" === \substr($prev, -1)) {
                        $blocks[$block][0] = \substr($blocks[$block][0], 0, -1);
                        $blocks[++$block] = ["", false];
                    }
                    $blocks[++$block] = [$row, "" !== $row];
                    continue;
                }
                // Continue the code block…
                $blocks[$block][0] .= "\n" . $row;
                $blocks[$block][1] = false;
                continue;
            }
            // Current block is a blank line…
            if ("" === $row) {
                $blocks[++$block] = ["", false];
                continue;
            }
            // Start of a tight header block?
            $n = \strspn($row, '#');
            if ($n > 0 && $n < 7 && false !== \strpos(" \t", \substr($row . ' ', $n, 1))) {
                $blocks[++$block] = [$row, true];
                continue;
            }
            // Start of a tight element block?
            if ('<' === $row[0]) {}
            // Start of a tight quote block?
            if ('>' === $row[0]) {
                $blocks[++$block] = [$row, true];
                continue;
            }
            // Start of a tight code block?
            if (false !== \strpos('`~', $row[0]) && ($n = \strspn($row, $row[0])) > 2) {
                // Character ‘`’ cannot exist in the info string if code block fence uses ‘`’ character(s)
                if ('`' === $row[0] && false !== \strpos(\substr($row, $n), '`')) {
                    $blocks[$block][0] .= "\n" . $row;
                    continue;
                }
                $blocks[++$block] = [$row, true];
                continue;
            }
            // End of a header block?
            if ('-' === $row[0] && \strlen($row) === ($n = \strspn($row, $row[0])) && "\n" !== \substr($prev, -1)) {
                $blocks[$block][0] .= "\n" . $row;
                continue;
            }
            // Start of a tight horizontal rule?
            if ('_' === $row[0]) {
                $test = \strtr($row, [
                    "\t" => "",
                    ' ' => ""
                ]);
                if (\strlen($test) === ($n = \strspn($test, $test[0])) && $n > 2) {
                    $blocks[++$block] = [$row, true];
                    continue;
                }
                // Not a tight horizontal rule…
                $blocks[$block][0] .= "\n" . $row;
                continue;
            }
            // Start of a tight list block?
            if (false !== \strpos('*+-', $row[0])) {
                if (1 === \strlen($row) || false !== \strpos(" \t", $row[1])) {
                    $blocks[++$block] = [$row, true];
                    continue;
                }
                // Start of a tight horizontal rule?
                $test = \strtr($row, [
                    "\t" => "",
                    ' ' => ""
                ]);
                if (\strlen($test) === ($n = \strspn($test, $test[0])) && $n > 2) {
                    $blocks[++$block] = [$row, true];
                    continue;
                }
                // Not a tight horizontal rule…
                $blocks[$block][0] .= "\n" . $row;
                continue;
            }
            // Start of a tight list block?
            $n = \strspn($row, '0123456789');
            if (false !== \strpos(').', \substr($row, $n, 1))) {
                if ($n + 1 === \strlen($row) || false !== \strpos(" \t", \substr($row, $n + 1, 1))) {
                    $blocks[++$block] = [$row, true];
                    continue;
                }
            }
            // Continue the current block…
            $blocks[$block][0] .= "\n" . $row;
            continue;
        }
        // Start a new block…
        $blocks[++$block] = [$row, "" !== $row];
    }
    return $blocks;
}

$content = file_get_contents(__DIR__ . D . 'test.md');

echo '<pre>';
foreach (split($content) as $v) {
    echo '<span style="border:2px solid;color:#' . ($v[1] ? '080' : '800') . ';display:block;margin:0 0 1px;">' . (\htmlspecialchars($v[0]) ?: '<br>') . '</span>';
}
echo '</pre>';

echo '<pre>';
echo \htmlspecialchars(join(split($content), static function () { return "<asdf>\n  <asdf>asdf</asdf>\n  <asdf>asdf</asdf>\n  <asdf>asdf</asdf>\n</asdf>"; }));
echo '</pre>';

exit;