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

        // Ensure directory exists
        if (!is_dir($this->storagePath)) {
            @mkdir($this->storagePath, 0777, true);
        }
    }

    public function isAllowed($ip)
    {
        $file = $this->storagePath . md5($ip) . '.json';
        $now = time();

        $data = [
            'start_time' => $now,
            'count' => 1
        ];

        if (file_exists($file)) {
            $content = @file_get_contents($file);
            if ($content) {
                $decoded = json_decode($content, true);
                if ($decoded && isset($decoded['start_time'])) {
                    // Cleanup old window
                    if ($now - $decoded['start_time'] <= $this->window) {
                        $data = $decoded;
                        $data['count']++;
                    }
                }
            }
        }

        @file_put_contents($file, json_encode($data));

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
