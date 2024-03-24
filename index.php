<?php namespace x\markdown__embed;

$content = <<<MD
asdf asdf asdf
asdf asdf asdf

<asdf:asdf>

 <asdf:asdf>

asdf asdf asdf

    asdf asdf asdf
    asdf asdf asdf

    <asdf:asdf>

    asdf asdf asdf

asdf asdf asdf

```
asdf asdf asdf
asdf asdf asdf

<asdf:asdf>

asdf asdf asdf
```

``` asdf
asdf asdf asdf
asdf asdf asdf

<asdf:asdf>

asdf asdf asdf
```

asdf asdf asdf

~~~
asdf asdf asdf
asdf asdf asdf

<asdf:asdf>

asdf asdf asdf
~~~

~~~ asdf
asdf asdf asdf
asdf asdf asdf

<asdf:asdf>

asdf asdf asdf
~~~

> asdf asdf asdf
> asdf asdf asdf
>
> <asdf:asdf>
>
> asdf asdf asdf
>
>     asdf asdf asdf
>     asdf asdf asdf
>
>     <asdf:asdf>
>
>     asdf asdf asdf
>
> asdf asdf asdf
>
> ```
> asdf asdf asdf
> asdf asdf asdf
>
> <asdf:asdf>
>
> asdf asdf asdf
> ```
>
> ``` asdf
> asdf asdf asdf
> asdf asdf asdf
>
> <asdf:asdf>
>
> asdf asdf asdf
> ```
>
> asdf asdf asdf
>
> ~~~
> asdf asdf asdf
> asdf asdf asdf
>
> <asdf:asdf>
>
> asdf asdf asdf
> ~~~
>
> ~~~ asdf
> asdf asdf asdf
> asdf asdf asdf
>
> <asdf:asdf>
>
> asdf asdf asdf
> ~~~

<asdf:asdf>
-----------

### <asdf:asdf>

asdf asdf asdf

1. asdf asdf asdf
   asdf asdf asdf

   <asdf:asdf>

   asdf asdf asdf

       asdf asdf asdf
       asdf asdf asdf

       <asdf:asdf>

       asdf asdf asdf

   asdf asdf asdf

   ```
   asdf asdf asdf
   asdf asdf asdf

   <asdf:asdf>

   asdf asdf asdf
   ```

   ``` asdf
   asdf asdf asdf
   asdf asdf asdf

   <asdf:asdf>

   asdf asdf asdf
   ```

   asdf asdf asdf

   ~~~
   asdf asdf asdf
   asdf asdf asdf

   <asdf:asdf>

   asdf asdf asdf
   ~~~

   ~~~ asdf
   asdf asdf asdf
   asdf asdf asdf

   <asdf:asdf>

   asdf asdf asdf
   ~~~
2. asdf asdf asdf
3. <asdf:asdf>
4. asdf asdf asdf
   1. asdf asdf asdf
   2. asdf asdf asdf
      asdf asdf asdf

      <asdf:asdf>

      asdf asdf asdf

          asdf asdf asdf
          asdf asdf asdf

          <asdf:asdf>

          asdf asdf asdf

      asdf asdf asdf

      ```
      asdf asdf asdf
      asdf asdf asdf

      <asdf:asdf>

      asdf asdf asdf
      ```

      ``` asdf
      asdf asdf asdf
      asdf asdf asdf

      <asdf:asdf>

      asdf asdf asdf
      ```

      asdf asdf asdf

      ~~~
      asdf asdf asdf
      asdf asdf asdf

      <asdf:asdf>

      asdf asdf asdf
      ~~~

      ~~~ asdf
      asdf asdf asdf
      asdf asdf asdf

      <asdf:asdf>

      asdf asdf asdf
      ~~~
   3. asdf asdf asdf
5. asdf asdf asdf

asdf asdf asdf

- - -

asdf asdf asdf

- asdf asdf asdf
  asdf asdf asdf

  <asdf:asdf>

  asdf asdf asdf

      asdf asdf asdf
      asdf asdf asdf

      <asdf:asdf>

      asdf asdf asdf

  asdf asdf asdf

  ```
  asdf asdf asdf
  asdf asdf asdf

  <asdf:asdf>

  asdf asdf asdf
  ```

  ``` asdf
  asdf asdf asdf
  asdf asdf asdf

  <asdf:asdf>

  asdf asdf asdf
  ```

  asdf asdf asdf

  ~~~
  asdf asdf asdf
  asdf asdf asdf

  <asdf:asdf>

  asdf asdf asdf
  ~~~

  ~~~ asdf
  asdf asdf asdf
  asdf asdf asdf

  <asdf:asdf>

  asdf asdf asdf
  ~~~
- asdf asdf asdf
- <asdf:asdf>
- asdf asdf asdf
  - asdf asdf asdf
  - asdf asdf asdf
    asdf asdf asdf

    <asdf:asdf>

    asdf asdf asdf

        asdf asdf asdf
        asdf asdf asdf

        <asdf:asdf>

        asdf asdf asdf

    asdf asdf asdf

    ```
    asdf asdf asdf
    asdf asdf asdf

    <asdf:asdf>

    asdf asdf asdf
    ```

    ``` asdf
    asdf asdf asdf
    asdf asdf asdf

    <asdf:asdf>

    asdf asdf asdf
    ```

    asdf asdf asdf

    ~~~
    asdf asdf asdf
    asdf asdf asdf

    <asdf:asdf>

    asdf asdf asdf
    ~~~

    ~~~ asdf
    asdf asdf asdf
    asdf asdf asdf

    <asdf:asdf>

    asdf asdf asdf
    ~~~
  - asdf asdf asdf
- asdf asdf asdf

asdf asdf asdf
MD;

function blocks(string $content, int $d = 0) {
    $block = -1;
    $blocks = [];
    $rows = \explode("\n", $content);
    foreach ($rows as $row) {
        while (false !== ($before = \strstr($row, "\t", true))) {
            $v = \strlen($before);
            $row = $before . \str_repeat(' ', 4 - $v % 4) . \substr($row, $v + 1);
        }
        $prefix = \str_repeat(' ', $d);
        if ($prefix === \substr($row, 0, $d)) {
            $row = \substr($row, $d);
        }
        $dent = \strspn($row, ' ');
        if ("" === $row) {
            $prefix = ""; // Do not indent empty block
        }
        if ($prev = $blocks[$block][0] ?? 0) {
            if (false !== \strpos('`~', $prev[0]) && ($n = \strspn($prev, $prev[0])) >= 3) {
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
                        // Convert last `\n` character in list block syntax to the next block if next row is not empty
                        // and is not a list block syntax
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
                        // Convert last `\n` character in list block syntax to the next block if next row is not empty
                        // and is not a list block syntax
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
                        // Convert last `\n` character in indented code block syntax to the next block if next row is
                        // not empty and is not an indented code block syntax
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
            if ("" === $row) {
                $blocks[++$block] = ["", false];
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
        if ('>' === $block[0][0]) {
            $block = \preg_replace('/^>(?>[ \t]?|$)/m', "", $block[0]);
			$block = content(blocks($block), $fn);
            $block = '> ' . \strtr($block, ["\n" => "\n> "]);
            $block = \preg_replace('/^>[ \t]?$/m', '>', $block);
            continue;
        }
		$test = \trim($block = $block[0]);
		if ('<' === $test[0] && '>' === \substr($test, -1)) {
			$block = \call_user_func($fn, $block);
		}
    }
    unset($block);
    return \implode("\n", $blocks);
}

// $content = '    ' . \strtr($content, ["\n" => "\n    "]);
// $content = \preg_replace('/^[ \t]+$/m', "", $content);

echo '<pre>';
foreach (blocks($content) as $v) {
    echo '<span style="border:2px solid;color:#' . ($v[1] ? '080' : '800') . ';display:block;margin:0 0 1px;">' . (\htmlspecialchars($v[0]) ?: '<br>') . '</span>';
}
echo '</pre>';

echo '<pre>';
echo \strtr(\htmlspecialchars(content(blocks($content), static function () { return '<asdf></asdf>'; })), ['&lt;asdf&gt;&lt;/asdf&gt;' => '<mark>&lt;asdf&gt;&lt;/asdf&gt;</mark>']);
echo '</pre>';

exit;