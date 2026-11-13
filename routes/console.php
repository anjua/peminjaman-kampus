<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('peminjaman:proses-otomatis')->everyFiveMinutes();
