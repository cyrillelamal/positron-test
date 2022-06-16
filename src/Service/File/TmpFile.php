<?php

namespace App\Service\File;

/**
 * Temporary file automatically destroyed by OS.
 */
class TmpFile
{
    private string $dir;
    private string $prefix;

    private string $tmpfname;
    /**
     * @var false|resource
     */
    private $handle;

    public function __construct(?string $dir = null, string $prefix = '')
    {
        $this->dir = $dir ?? sys_get_temp_dir();
        $this->prefix = $prefix ?? '';

        $this->tmpfname = tempnam($this->dir, $this->prefix);
        $this->handle = fopen($this->tmpfname, 'r+t');
    }

    /**
     * @param string $data
     * @return bool|int
     */
    public function write(string $data): bool|int
    {
        return fwrite($this->handle, $data);
    }

    public function getTmpfname(): string
    {
        return $this->tmpfname;
    }

    public function close(): void
    {
        fclose($this->handle);
        unlink($this->tmpfname);
    }
}
