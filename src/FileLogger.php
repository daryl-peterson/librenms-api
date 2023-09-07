<?php

namespace LibrenmsApiClient;

use Psr\Log\AbstractLogger;

/**
 * Class description.
 *
 * @category
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       1.0.0
 */
class FileLogger extends AbstractLogger
{
    public const DEBUG_LEVEL = 100;
    public const INFO_LEVEL = 200;
    public const NOTICE_LEVEL = 250;
    public const WARNING_LEVEL = 300;
    public const ERROR_LEVEL = 400;
    public const CRITICAL_LEVEL = 500;
    public const ALERT_LEVEL = 550;
    public const EMERGENCY = 550;

    public const LEVEL_MAP = [
        'DEBUG' => self::DEBUG_LEVEL,
        'INFO' => self::INFO_LEVEL,
        'NOTICE' => self::INFO_LEVEL,
        'WARNING' => self::WARNING_LEVEL,
        'ERROR' => self::ERROR_LEVEL,
        'CRITICAL' => self::CRITICAL_LEVEL,
        'ALERT' => self::ALERT_LEVEL,
        'EMERGENCY' => self::EMERGENCY,
    ];

    protected Cache $cache;
    private array $keys;
    private string|null $file;
    private int|string|null $level;

    public function __construct()
    {
        $this->cache = Cache::getInstance();
        $this->level = $this->cache->get(Cache::LOG_LEVEL);
        $this->file = $this->cache->get(Cache::LOG_FILE);

        if (!isset($this->level)) {
            $this->level = self::ERROR_LEVEL;
        } else {
            $this->level = $this->getLevelInt($this->level);
        }

        if (!isset($this->file)) {
            $tmp = sys_get_temp_dir();
            $this->file = $tmp.'/api-client.log';
        }

        $this->keys = [
            'class',
            'function',
            'line',
        ];
    }

    /**
     * @param mixed   $level
     * @param mixed[] $context
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $levelInt = $this->getLevelInt($level);

        if ($levelInt < $this->level) {
            return;
        }

        $msg = $this->format($level, $message, $context);
        $this->ckFileSize();
        $this->write($msg);
    }

    private function ckFileSize()
    {
        $fs = filesize($this->file);

        // @codeCoverageIgnoreStart
        if (!$fs) {
            return;
        }

        if ($fs > 2000000) {
            $fh = fopen($this->file, 'w');

            if ($fh) {
                fclose($fh);
            }
        }
        // @codeCoverageIgnoreEnd
    }

    private function write(string $message)
    {
        $fh = fopen($this->file, 'a+');
        if (!$fh) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }
        fputs($fh, $message);
        fclose($fh);
    }

    private function getLevelInt(int|string $level)
    {
        if (is_int($level)) {
            return $level;
        }
        $level = strtoupper($level);
        if (key_exists($level, self::LEVEL_MAP)) {
            return self::LEVEL_MAP[$level];
        }

        return self::CRITICAL_LEVEL;
    }

    private function format($level, string|\Stringable $message, array $record)
    {
        $message = $this->getMessage($level, $message, $record);
        $context = $this->getContext($record);

        return $message.$context."\n";
    }

    private function getMessage($level, string|\Stringable $message, array $record)
    {
        $data = [];

        foreach ($record as $key => $value) {
            if (!in_array(strtolower($key), $this->keys)) {
                continue;
            }
            $data[$key] = $value;
        }

        $date = date('Y-m-d H:i:s');
        $now = \DateTime::createFromFormat('U.u', microtime(true));

        if ($now) {
            $date = $now->format('Y-m-d H:i:s.u');
        }

        $padLen = 10;
        $pad_str = str_repeat(' ', $padLen);

        $msg = "\n";
        $msg .= str_repeat('-', 80)."\n";
        $msg .= substr('LEVEL'.$pad_str, 0, $padLen)." : $level \n";
        $msg .= substr('DATE'.$pad_str, 0, $padLen)." : $date \n";

        foreach ($this->keys as $key_name) {
            if (isset($data[$key_name])) {
                $key_name_str = strtoupper($key_name).$pad_str;
                $msg .= substr($key_name_str, 0, $padLen).' : '.$data[$key_name]."\n";
            }
        }
        $msg .= "\n";
        $msg .= substr('MESSAGE'.$pad_str, 0, $padLen)." :\n".$message."\n\n";
        $msg .= substr('CONTEXT'.$pad_str, 0, $padLen)." :\n";

        return $msg;
    }

    private function getContext(array $context): string
    {
        foreach ($context as $key => $value) {
            if (!in_array(strtolower($key), $this->keys)) {
                continue;
            }
            unset($context[$key]);
        }

        foreach ($context as $key => $value) {
            $type = gettype($value);
            $class = null;
            if ('object' === $type) {
                $class = get_class($value);
                if ('stdClass' !== $class) {
                    $context[$key] = (array) $value;
                }
            }

            if ('array' === $type) {
                if (count($value) > 10) {
                    $value = array_slice($value, 0, 10);
                    $context[$key] = $value;
                }
            }
        }

        $context = json_decode(json_encode($context, JSON_PRETTY_PRINT), true);
        $context = json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $context = str_replace('\u0000*', '', $context);
        $context = str_replace('\u0000', '', $context);

        return $context;
    }
}
