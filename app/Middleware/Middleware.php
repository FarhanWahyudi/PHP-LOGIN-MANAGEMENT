<?php
    namespace Hans\Belajar\PHP\MVC\Middleware;

    interface Middleware {
        function before(): void;
    }