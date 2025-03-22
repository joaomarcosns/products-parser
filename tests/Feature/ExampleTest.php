<?php

namespace Tests\Feature;

test('sum', function () {
    $result = 1 + 2;

    expect($result)->toBe(3);
});
