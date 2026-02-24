<?php

namespace App\Services;

class RateLimitService
{
    private $limit = 100; // requests
    private $window = 3600; // 1 hour in seconds
    private $storagePath;

    public function __construct($limit = 100, $window = 3600)
    {
        $this->limit = $limit;
        $this->window = $window;
        $this->storagePath = __DIR__ . '/../../logs/ratelimit/';
    }

    public function isAllowed($ip)
    {
        $file = $this->storagePath . md5($ip) . '.json';
        $now = time();

        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);

            // Cleanup old window
            if ($now - $data['start_time'] > $this->window) {
                $data = [
                    'start_time' => $now,
                    'count' => 1
                ];
            } else {
                $data['count']++;
            }
        } else {
            $data = [
                'start_time' => $now,
                'count' => 1
            ];
        }

        file_put_contents($file, json_encode($data));

        return $data['count'] <= $this->limit;
    }

    public function getRemaining($ip)
    {
        $file = $this->storagePath . md5($ip) . '.json';
        if (!file_exists($file)) return $this->limit;

        $data = json_decode(file_get_contents($file), true);
        return max(0, $this->limit - $data['count']);
    }
}
