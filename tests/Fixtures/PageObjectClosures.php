<?php

declare(strict_types=1);

/**
 * Closures whose source contains page-object detection tokens.
 * Kept in a separate file so that PageObjectTestFilterTest.php itself
 * does not contain those tokens and is not flagged as a browser test
 * during suite assembly.
 */
return [
    'with_page_function_call' => function (): void {
        page('SomePage');
    },
    'with_static_open_call' => function (): void {
        SomePage::open();
    },
];