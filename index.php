<?php namespace x\markdown__embed;

function blocks(string $content) {
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
			if ($row && '>' === $row[0] && '>' === $prev[0]) {
				$blocks[$block][0] .= "\n" . $row;
				continue;
			}
            if (false !== \strpos('`~', $prev[0]) && ($n = \strspn($prev, $prev[0])) >= 3) {
                $test = \strstr($prev, "\n", true) ?: $prev;
                if ('`' === $prev[0] && false !== \strpos(\substr($test, $n), '`')) {
                    $blocks[++$block] = [$row, "" !== $row];
                    continue;
                }
                if ($row === \str_repeat($prev[0], $n)) {
                    $blocks[$block++][0] .= "\n" . $row;
                    continue;
                }
                $blocks[$block][0] .= "\n" . $row;
                $blocks[$block][1] = false;
                continue;
            }
            if (false !== \strpos('*+-', $prev[0]) && false !== \strpos(" \t", $prev[1] ?? "")) {
                if ("" !== $row && $dent < 2) {
                    if ("\n" === \substr($prev, -1)) {
                        $blocks[$block][0] = \substr($blocks[$block][0], 0, -1);
                        $blocks[++$block] = ["", false];
                    }
                    $blocks[++$block] = [$row, "" !== $row];
                    continue;
                }
                $blocks[$block][0] .= "\n" . $row;
                continue;
            }
            $n = \strspn($prev, '0123456789');
            if ($n > 9) {
                $blocks[++$block] = [$row, "" !== $row];
                continue;
            }
            if (false !== \strpos(').', \substr($prev, $n, 1)) && false !== \strpos(" \t", \substr($prev, $n + 1, 1))) {
                if ("" !== $row && $dent < 3) {
                    if ("\n" === \substr($prev, -1)) {
                        $blocks[$block][0] = \substr($blocks[$block][0], 0, -1);
                        $blocks[++$block] = ["", false];
                    }
                    $blocks[++$block] = [$row, "" !== $row];
                    continue;
                }
                $blocks[$block][0] .= "\n" . $row;
                continue;
            }
            if (\strspn($prev, ' ') >= 4) {
                if ("" !== $row && $dent < 4) {
                    if ("\n" === \substr($prev, -1)) {
                        $blocks[$block][0] = \substr($blocks[$block][0], 0, -1);
                        $blocks[++$block] = ["", false];
                    }
                    $blocks[++$block] = [$row, "" !== $row];
                    continue;
                }
                $blocks[$block][0] .= "\n" . $row;
                $blocks[$block][1] = false;
                continue;
            }
			// Blank line
            if ("" === $row) {
                $blocks[++$block] = ["", false];
                continue;
            }
			// Start of tight quote block
            if ('>' === $row[0]) {
                $blocks[++$block] = [$row, true];
                continue;
            }
			// Start of tight list block
            if (false !== \strpos('*+-', $row[0]) && false !== \strpos(" \t", $row[1] ?? "")) {
                $blocks[++$block] = [$row, true];
                continue;
            }
			// Start of tight list block
            $n = \strspn($row, '0123456789');
            if (false !== \strpos(').', \substr($row, $n, 1)) && false !== \strpos(" \t", \substr($row, $n + 1, 1))) {
                $blocks[++$block] = [$row, true];
                continue;
            }
            $blocks[$block][0] .= "\n" . $row;
            continue;
        }
        $blocks[++$block] = [$row, "" !== $row];
    }
    return $blocks;
}

function content(array $blocks, callable $fn) {
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
            $block = content(blocks(\implode("\n", $parts)), $fn);
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
            $block = content(blocks(\implode("\n", $parts)), $fn);
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
            $block = content(blocks(\implode("\n", $parts)), $fn);
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
            $dent = \substr($block, 0, $n);
            $block = \call_user_func($fn, \substr($block, $n));
            $parts = \explode("\n", $block);
            foreach ($parts as $k => $v) {
                if ("" === $v) {
                    continue;
                }
                $parts[$k] = $dent . $v;
            }
            $block = \implode("\n", $parts);
        }
    }
    unset($block);
    return \implode("\n", $blocks);
}

$content = file_get_contents(__DIR__ . D . 'test.md');

echo '<pre>';
foreach (blocks($content) as $v) {
    echo '<span style="border:2px solid;color:#' . ($v[1] ? '080' : '800') . ';display:block;margin:0 0 1px;">' . (\htmlspecialchars($v[0]) ?: '<br>') . '</span>';
}
echo '</pre>';

echo '<pre>';
echo \htmlspecialchars(content(blocks($content), static function () { return "<asdf>\n  <asdf>asdf</asdf>\n  <asdf>asdf</asdf>\n  <asdf>asdf</asdf>\n</asdf>"; }));
echo '</pre>';

exit;