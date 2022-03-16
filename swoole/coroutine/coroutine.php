<?php


use Swoole\Coroutine;
use function Swoole\Coroutine\run;

echo "main start\n";
run(function () {
    echo "coro " . Coroutine::getcid() . " start\n";
    Coroutine::create(function () {
        echo "coro " . Coroutine::getcid() . " start\n";
        Coroutine::sleep(.2);
        echo "coro " . Coroutine::getcid() . " end\n";
    });
    echo "coro " . Coroutine::getcid() . " do not wait children coroutine\n";
    Coroutine::sleep(.1);
    echo "coro " . Coroutine::getcid() . " end\n";
});
echo "end\n";

/*
main start
coro 1 start
coro 2 start
coro 1 do not wait children coroutine
coro 1 end
coro 2 end
end
*/
