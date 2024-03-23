<?php namespace x\markdown__embed;

$content = <<<MD
asdf asdf asdf
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
MD;

function chunk(string $content, int $d = 0) {
    $prev = "";
    $r = [];
    foreach (\explode("\n", $content) as $row) {
        while (false !== ($before = \strstr($row, "\t", true))) {
            $v = \strlen($before);
            $row = $before . \str_repeat(' ', 4 - $v % 4) . \substr($row, $v + 1);
        }
        $prefix = \str_repeat(' ', $d);
        if ($prefix === \substr($prev, 0, $d)) {
            $prev = \substr($prev, $d);
        }
        if ($prefix === \substr($row, 0, $d)) {
            $row = \substr($row, $d);
        }
        $dent = \strspn($row, ' ');
        if ("" === $row) {
            $prefix = ""; // Do not indent empty chunk(s)
        }
        if ("" !== $prev && isset($r[$last = \count($r) - 1])) {
            if (("" === $row || $dent >= 4) && \strspn($r[$last][0], ' ') >= 4) {
                $r[$last][0] .= "\n" . $row;
                continue;
            }
            if ("" === $row || $dent > 0) {
                $n = \strspn($prev, '0123456789');
                if ($n > 9) {
                    $r[] = [$prev = $prefix . $row, true];
                    continue;
                }
                if (false !== \strpos(').', \substr($prev, $n, 1)) && false !== \strpos(" \t", \substr($prev, $n + 1, 1))) {
                    $r[$last][0] .= "\n" . $prefix . $row;
                    continue;
                }
                $r[] = [$prev = $prefix . $row, true];
                continue;
            }
            if (isset($r[$last][0][0])) {
                if ('>' === $r[$last][0][0] && '>' === \substr($row, 0, 1)) {
                    $r[$last][0] .= "\n" . $prefix . $row;
                    continue;
                }
                if (false !== \strpos('`~', $r[$last][0][0]) && ($n = \strspn($r[$last][0], $r[$last][0][0])) >= 3) {
                    if ("\n" . \str_repeat($r[$last][0][0], $n) === \substr($r[$last][0], -($n + 1))) {
                        $r[] = [$prev = $prefix . $row, true];
                        continue;
                    }
                    $r[$last][0] .= "\n" . $prefix . $row;
                    $r[$last][1] = false;
                    continue;
                }
            }
        }
        if ($dent >= 4) {
            $r[] = [$prev = $prefix . $row, false];
            continue;
        }
        $r[] = [$prev = $prefix . $row, true];
    }
    return $r;
}

// $content = '    ' . \strtr($content, ["\n" => "\n    "]);
// $content = \preg_replace('/^[ \t]+$/m', "", $content);

echo '<pre>';
foreach (chunk($content) as $v) {
    echo '<span style="border:2px solid;color:#' . ($v[1] ? '080' : '800') . ';display:block;margin:0 0 1px;">' . (\htmlspecialchars($v[0]) ?: '<br>') . '</span>';
}
echo '</pre>';

exit;