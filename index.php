<?php namespace x\markdown__embed;

$content = <<<MD
asdf asdf asdf
asdf asdf asdf

<asdf:asdf>

asdf asdf asdf

    <asdf:asdf>

asdf asdf asdf

```
<asdf:asdf>
```

``` asdf
<asdf:asdf>
```

asdf asdf asdf

~~~
<asdf:asdf>
~~~

~~~ asdf
<asdf:asdf>
~~~

> asdf asdf asdf
> asdf asdf asdf
>
> <asdf:asdf>
>
> asdf asdf asdf
>
>     <asdf:asdf>
>
> asdf asdf asdf
>
> ```
> <asdf:asdf>
> ```
>
> ``` asdf
> <asdf:asdf>
> ```
>
> asdf asdf asdf
>
> ~~~
> <asdf:asdf>
> ~~~
>
> ~~~ asdf
> <asdf:asdf>
> ~~~

<asdf:asdf>
-----------

### <asdf:asdf>

asdf asdf asdf

1. asdf asdf asdf
   asdf asdf asdf

   <asdf:asdf>

   asdf asdf asdf

       <asdf:asdf>

   asdf asdf asdf

   ```
   <asdf:asdf>
   ```

   ``` asdf
   <asdf:asdf>
   ```

   asdf asdf asdf

   ~~~
   <asdf:asdf>
   ~~~

   ~~~ asdf
   <asdf:asdf>
   ~~~
2. asdf asdf asdf
3. <asdf:asdf>
4. asdf asdf asdf
   1. asdf asdf asdf
   2. asdf asdf asdf
      asdf asdf asdf

      <asdf:asdf>

      asdf asdf asdf

          <asdf:asdf>

      asdf asdf asdf

      ```
      <asdf:asdf>
      ```

      ``` asdf
      <asdf:asdf>
      ```

      asdf asdf asdf

      ~~~
      <asdf:asdf>
      ~~~

      ~~~ asdf
      <asdf:asdf>
      ~~~
   3. asdf asdf asdf
5. asdf asdf asdf
MD;

function blocks(string $content, int $d = 0) {
    $prev = "";
    $r = [];
    foreach (\explode("\n", $content) as $current) {
        $prefix = \str_repeat(' ', $d);
        if ($prefix === \substr($current, 0, $d)) {
            $current = \substr($current, $d);
        }
        if ($prefix === \substr($prev, 0, $d)) {
            $prev = \substr($prev, $d);
        }
        $dent = \strspn($current, ' ');
        if ("" === $current) {
            $prefix = ""; // Do not indent empty block(s)
        }
        if ("" !== $prev && isset($r[$last = \count($r) - 1])) {
            if ("" === $current || $dent > 0) {
                $n = \strspn($prev, '0123456789');
                if ($n > 9) {
                    $r[] = $prev = $prefix . $current;
                    continue;
                }
                if (false !== \strpos(').', \substr($prev, $n, 1)) && false !== \strpos(" \t", \substr($prev, $n + 1, 1))) {
                    $r[$last] .= "\n" . $prefix . $current;
                    continue;
                }
                $r[] = $prev = $prefix . $current;
                continue;
            }
            if (isset($prev[0])) {
                if ('>' === $prev[0] && '>' === \substr($current, 0, 1)) {
                    $r[$last] .= "\n" . $prefix . $current;
                    continue;
                }
                if (false !== \strpos('`~', $prev[0]) && ($n = \strspn($prev, $prev[0])) >= 3) {
                    if ("\n" . \str_repeat($prev[0], $n) === \substr($r[$last], -($n + 1))) {
                        $r[] = $prev = $prefix . $current;
                        continue;
                    }
                    $r[$last] .= "\n" . $prefix . $current;
                    continue;
                }
            }
            $r[$last] .= "\n" . $prefix . $current;
            continue;
        }
        $r[] = $prev = $prefix . $current;
    }
    return $r;
}

$content = '    ' . \strtr($content, ["\n" => "\n    "]);
$content = \preg_replace('/^[ \t]+$/m', "", $content);

echo '<pre>';
foreach (blocks($content, 4) as $v) {
    echo '<span style="border:1px solid;display:block;">' . (\htmlspecialchars($v) ?: '<br>') . '</span>';
}
echo '</pre>';

exit;