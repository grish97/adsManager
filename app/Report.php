<?php

namespace app;

class Report
{
    public $session;

    public function __construct($session) {
        $this->session = $session;

    }
}