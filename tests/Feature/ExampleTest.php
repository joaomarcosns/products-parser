<?php

declare(strict_types=1);

namespace Tests\Feature;

test('sum', function () {
    $result = 1 + 2;

    expect($result)->toBe(3);
});
