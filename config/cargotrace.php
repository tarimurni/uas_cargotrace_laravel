<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CargoTrace Delay Threshold (in Hours)
    |--------------------------------------------------------------------------
    |
    | Batas ambang jam selisih antara estimated_arrival dan expected_arrival
    | sebelum pengiriman ditandai sebagai 'is_delayed = true'.
    | Default: 12 jam (dapat diubah via CARGOTRACE_DELAY_THRESHOLD_HOURS di .env)
    |
    */
    'delay_threshold_hours' => (int) env('CARGOTRACE_DELAY_THRESHOLD_HOURS', 12),
];
