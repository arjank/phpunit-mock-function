<?php

namespace Potherca\Phpunit\MockFunction;

function requireOnce($file)
{
    static $mockedFiles = [];

    $getCallerNamespace  = function () {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

        $caller = array_pop($trace);

        $class = $caller['class'];

        $position = strrpos($class,'\\');

        return (string) substr($class, 0, $position);
    };

    $contents = file_get_contents($file);

    /* @FIXME: Use parser/lexer instead of preg match to find namespace. */
    $containsNamespaceDeclaration = (bool) preg_match('/namespace [a-zA-Z_]+[a-zA-Z0-9_\\\\]*;/im', $contents);

    if ($containsNamespaceDeclaration === true) {
        $key = '_'.md5($file);

        /* $file contents contains a namespace, so just `require_once` it... */
        if (array_key_exists($file, $mockedFiles) === false) {
            /** @noinspection PhpIncludeInspection */
            $mockedFiles[$key] = require $file;
        }
    } else {
        /* $file contents does not contain a namespace, let's add one... */

        $namespace = $getCallerNamespace();

        $key = '_'.md5($namespace.'\\'.$file);

        if (array_key_exists($key, $mockedFiles) === false) {

            // @FIXME: Use parser/lexer instead of `substr`
            $body = substr($contents, 5);

            $template = new \Text_Template(__DIR__.'/namespace.tpl', '{{', '}}');

            $template->setVar([
                'contents' => $body,
                'namespace' => $namespace,
            ]);

            $eval = $template->render();

            $mockedFiles[$key] = eval($eval);
        }
    }

    return $mockedFiles[$key];
}

/*EOF*/
